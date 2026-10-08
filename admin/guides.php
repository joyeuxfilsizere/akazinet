<?php
/**
 * Career Guides management.
 *
 * Editorial CRUD for the guides table created by migration17.sql.
 * Drafts are hidden from the public pages, the sitemap, and the nav.
 */

require_once __DIR__ . '/../config/database.php';
require_admin_login();

/**
 * Slugify a title. Kept dependency-free and ASCII-only so slugs stay stable
 * across the public URLs already indexed by Google.
 */
function akazinet_slugify($text)
{
    $text = strtolower(trim((string)$text));
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim(preg_replace('/-+/', '-', $text), '-');
    return $text !== '' ? $text : 'guide';
}

/**
 * Resolve a unique slug, appending -2, -3 ... on collision.
 * $ignoreId lets an edit keep its own slug instead of colliding with itself.
 */
function akazinet_unique_guide_slug($pdo, $slug, $ignoreId = 0)
{
    $base = $slug;
    $suffix = 2;
    while (true) {
        $check = $pdo->prepare("SELECT id FROM guides WHERE slug = ? AND id != ? LIMIT 1");
        $check->execute([$slug, (int)$ignoreId]);
        if (!$check->fetch()) {
            return $slug;
        }
        $slug = $base . '-' . $suffix++;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    if (isset($_POST['delete_guide'])) {
        $pdo->prepare("DELETE FROM guides WHERE id = ?")->execute([(int)$_POST['guide_id']]);
        set_flash('success', 'Guide deleted.');
        redirect('/admin/guides');
    }

    if (isset($_POST['toggle_status'])) {
        $id = (int)$_POST['guide_id'];
        $pdo->prepare("UPDATE guides SET status = IF(status = 'published', 'draft', 'published') WHERE id = ?")->execute([$id]);
        set_flash('success', 'Guide status updated.');
        redirect('/admin/guides');
    }

    if (isset($_POST['toggle_featured'])) {
        $pdo->prepare("UPDATE guides SET is_featured = NOT is_featured WHERE id = ?")->execute([(int)$_POST['guide_id']]);
        set_flash('success', 'Featured flag updated.');
        redirect('/admin/guides');
    }

    if (isset($_POST['save_guide'])) {
        $editId = (int)($_POST['edit_id'] ?? 0);
        $title = trim((string)($_POST['title'] ?? ''));
        $excerpt = trim((string)($_POST['excerpt'] ?? ''));
        $content = trim((string)($_POST['content'] ?? ''));
        $category = trim((string)($_POST['category'] ?? '')) ?: 'Career Advice';
        $slugInput = trim((string)($_POST['slug'] ?? ''));
        $metaTitle = trim((string)($_POST['meta_title'] ?? ''));
        $metaDescription = trim((string)($_POST['meta_description'] ?? ''));
        $readMinutes = max(1, min(60, (int)($_POST['read_minutes'] ?? 4)));
        $status = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $redirectBack = '/admin/guides' . ($editId ? '?edit=' . $editId : '?action=add');

        // FAQ rows arrive as parallel arrays. Empty question/answer pairs are
        // dropped rather than stored, so an admin clearing the last row does
        // not leave an empty FAQPage node behind.
        $faqRows = [];
        $faqQuestions = (array)($_POST['faq_question'] ?? []);
        $faqAnswers = (array)($_POST['faq_answer'] ?? []);
        foreach ($faqQuestions as $i => $rawQuestion) {
            $question = trim((string)$rawQuestion);
            $answer = trim((string)($faqAnswers[$i] ?? ''));
            if ($question !== '' && $answer !== '') {
                $faqRows[] = ['question' => $question, 'answer' => $answer];
            }
        }

        // Sources are one per line in a textarea.
        $sourceRows = [];
        foreach (preg_split('/\R/', (string)($_POST['sources'] ?? '')) as $line) {
            $line = trim($line);
            if ($line !== '') {
                $sourceRows[] = $line;
            }
        }

        $faqJson = $faqRows
            ? json_encode($faqRows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : null;
        $sourcesJson = $sourceRows
            ? json_encode($sourceRows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : null;

        $errors = [];
        if ($title === '') { $errors[] = 'Title is required.'; }
        if ($content === '') { $errors[] = 'Content is required.'; }
        if (mb_strlen($title) > 200) { $errors[] = 'Title must be 200 characters or fewer.'; }
        if (mb_strlen($excerpt) > 500) { $errors[] = 'Excerpt must be 500 characters or fewer.'; }
        if (mb_strlen($metaTitle) > 200) { $errors[] = 'Meta title must be 200 characters or fewer.'; }
        if (mb_strlen($metaDescription) > 300) { $errors[] = 'Meta description must be 300 characters or fewer.'; }

        if ($errors) {
            set_flash('error', implode(' ', $errors));
            redirect($redirectBack);
        }

        $slug = $slugInput !== '' ? akazinet_slugify($slugInput) : akazinet_slugify($title);
        $slug = akazinet_unique_guide_slug($pdo, $slug, $editId);

        if ($editId) {
            // Preserve published_at when re-saving an already-published guide so
            // the sitemap lastmod does not jump on every edit.
            $pdo->prepare("
                UPDATE guides SET
                    title = ?, slug = ?, excerpt = ?, content = ?, category = ?,
                    meta_title = ?, meta_description = ?, is_featured = ?,
                    read_minutes = ?, status = ?, faq_json = ?, sources_json = ?,
                    published_at = IF(status <> 'published' AND ? = 'published', NOW(), published_at)
                WHERE id = ?
            ")->execute([
                $title, $slug, $excerpt, $content, $category,
                $metaTitle, $metaDescription, $isFeatured,
                $readMinutes, $status, $faqJson, $sourcesJson, $status, $editId,
            ]);
            set_flash('success', 'Guide updated.');
        } else {
            $pdo->prepare("
                INSERT INTO guides
                    (title, slug, excerpt, content, category, meta_title, meta_description,
                     is_featured, read_minutes, status, faq_json, sources_json, published_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ")->execute([
                $title, $slug, $excerpt, $content, $category,
                $metaTitle, $metaDescription, $isFeatured,
                $readMinutes, $status, $faqJson, $sourcesJson,
                $status === 'published' ? date('Y-m-d H:i:s') : null,
            ]);
            set_flash('success', 'Guide created.');
        }

        redirect('/admin/guides');
    }
}

$guides = $pdo->query("
    SELECT id, title, slug, category, status, is_featured, read_minutes, views,
           published_at, updated_at
    FROM guides
    ORDER BY status ASC, is_featured DESC, published_at DESC, id DESC
")->fetchAll();

$editGuide = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM guides WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editGuide = $stmt->fetch() ?: null;
}

$showForm = isset($_GET['action']) || $editGuide !== null;
$existingCategories = $pdo->query("SELECT DISTINCT category FROM guides ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

$totalWords = 0;
foreach ($pdo->query("SELECT content FROM guides") as $row) {
    $totalWords += str_word_count(strip_tags((string)$row['content']));
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Career Guides</h1>
        <p class="text-sm text-gray-500 mt-1">
            <?= count($guides) ?> guide(s) &middot; ~<?= number_format($totalWords) ?> words of original content.
            Guides are indexed by Google and appear in the sitemap only while published.
        </p>
    </div>
    <div class="flex items-center space-x-2">
        <a href="/pages/guides" target="_blank" class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
            <i class="fas fa-external-link-alt mr-1"></i> View public page
        </a>
        <a href="/admin/guides?action=add" class="px-4 py-2 text-sm bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
            <i class="fas fa-plus mr-1"></i> New guide
        </a>
    </div>
</div>

<?php if ($showForm): ?>
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
    <h2 class="text-lg font-bold text-gray-900 mb-5">
        <?= $editGuide ? 'Edit guide' : 'Write a new guide' ?>
    </h2>

    <form method="POST" class="space-y-5">
        <input type="hidden" name="csrf_token" value="<?= sanitize(csrf_token()) ?>">
        <input type="hidden" name="save_guide" value="1">
        <?php if ($editGuide): ?>
        <input type="hidden" name="edit_id" value="<?= (int)$editGuide['id'] ?>">
        <?php endif; ?>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2" for="title">Title <span class="text-red-500">*</span></label>
            <input type="text" id="title" name="title" required maxlength="200" value="<?= sanitize($editGuide['title'] ?? '') ?>"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2" for="slug">URL slug</label>
                <input type="text" id="slug" name="slug" value="<?= sanitize($editGuide['slug'] ?? '') ?>"
                    placeholder="leave blank to generate from title"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <p class="text-xs text-gray-500 mt-1">Changing this breaks existing links and rankings.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2" for="category">Category</label>
                <input type="text" id="category" name="category" list="guide-categories" value="<?= sanitize($editGuide['category'] ?? 'Career Advice') ?>"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <datalist id="guide-categories">
                    <?php foreach ($existingCategories as $cat): ?>
                    <option value="<?= sanitize($cat) ?>"></option>
                    <?php endforeach; ?>
                </datalist>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2" for="excerpt">Excerpt</label>
            <textarea id="excerpt" name="excerpt" rows="2" maxlength="500"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"><?= sanitize($editGuide['excerpt'] ?? '') ?></textarea>
            <p class="text-xs text-gray-500 mt-1">Used on cards and as the meta description fallback.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2" for="content">Content <span class="text-red-500">*</span></label>
            <textarea id="content" name="content" rows="20" required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg font-mono text-sm outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"><?= sanitize($editGuide['content'] ?? '') ?></textarea>
            <p class="text-xs text-gray-500 mt-1">
                Allowed HTML: p, br, strong, b, em, i, u, s, a, ul, ol, li, blockquote, h2, h3, h4, h5,
                table, thead, tbody, tfoot, tr, th, td, caption, hr, code, pre, sup, sub, dl, dt, dd.
                Scripts and event handlers are stripped on render.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2" for="meta_title">Meta title</label>
                <input type="text" id="meta_title" name="meta_title" maxlength="200" value="<?= sanitize($editGuide['meta_title'] ?? '') ?>"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2" for="meta_description">Meta description</label>
                <input type="text" id="meta_description" name="meta_description" maxlength="300" value="<?= sanitize($editGuide['meta_description'] ?? '') ?>"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2" for="status">Status</label>
                <select id="status" name="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    <option value="draft" <?= ($editGuide['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft (hidden)</option>
                    <option value="published" <?= ($editGuide['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2" for="read_minutes">Read time (minutes)</label>
                <input type="number" id="read_minutes" name="read_minutes" min="1" max="60" value="<?= (int)($editGuide['read_minutes'] ?? 4) ?>"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>
            <div class="flex items-end pb-3">
                <label class="flex items-center space-x-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_featured" value="1" <?= !empty($editGuide['is_featured']) ? 'checked' : '' ?>
                        class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary">
                    Feature on the guides index
                </label>
            </div>
        </div>

        <div class="border-t border-gray-200 pt-6">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Frequently asked questions</label>
                    <p class="text-xs text-gray-500 mt-1">
                        Each pair renders as an expandable section and as FAQPage schema. Keep answers self-contained, at least a full sentence, and specific to this guide.
                    </p>
                </div>
                <button type="button" id="add-faq-row" class="shrink-0 ml-4 px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition-colors">
                    <i class="fas fa-plus mr-1.5"></i>Add question
                </button>
            </div>

            <div id="faq-rows" class="space-y-4 mt-4">
                <?php
                $existingFaq = [];
                if (!empty($editGuide['faq_json'])) {
                    $decoded = json_decode((string)$editGuide['faq_json'], true);
                    if (is_array($decoded)) {
                        $existingFaq = $decoded;
                    }
                }
                // Always render one blank row for a new guide.
                if (empty($existingFaq)) {
                    $existingFaq = [['question' => '', 'answer' => '']];
                }
                foreach ($existingFaq as $i => $qa):
                    $q = (string)($qa['question'] ?? '');
                    $a = (string)($qa['answer'] ?? '');
                    ?>
                <div class="faq-row grid grid-cols-1 md:grid-cols-2 gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5" for="faq_question_<?= $i ?>">Question</label>
                        <input type="text" id="faq_question_<?= $i ?>" name="faq_question[]" value="<?= sanitize($q) ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5" for="faq_answer_<?= $i ?>">Answer</label>
                        <textarea id="faq_answer_<?= $i ?>" name="faq_answer[]" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"><?= sanitize($a) ?></textarea>
                    </div>
                    <div class="md:col-span-2 text-right">
                        <button type="button" class="remove-faq-row text-xs text-red-600 hover:text-red-700 font-medium">
                            <i class="fas fa-trash mr-1"></i>Remove
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="border-t border-gray-200 pt-6">
            <label class="block text-sm font-medium text-gray-700 mb-2" for="sources">Sources</label>
            <p class="text-xs text-gray-500 mb-2">
                One per line. Shown to readers as attribution, so salary figures and legal references are traceable.
            </p>
            <textarea id="sources" name="sources" rows="4"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"><?php
                if (!empty($editGuide['sources_json'])) {
                    $decoded = json_decode((string)$editGuide['sources_json'], true);
                    if (is_array($decoded)) {
                        echo sanitize(implode("\n", array_map('strval', $decoded)));
                    }
                }
            ?></textarea>
        </div>

        <div class="flex items-center space-x-3 pt-2">
            <button type="submit" class="px-5 py-3 bg-primary hover:bg-primary-dark text-white text-sm font-semibold rounded-lg transition-colors">
                <i class="fas fa-save mr-2"></i><?= $editGuide ? 'Save changes' : 'Create guide' ?>
            </button>
            <a href="/admin/guides" class="px-5 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">Cancel</a>
        </div>
    </form>
</div>
<?php endif; ?>

<script>
// FAQ rows are added client-side with plain DOM construction. InnerHTML with
// interpolated values would be the obvious shortcut and an injection risk, so
// createElement/textContent is used for the input values.
(function () {
    var container = document.getElementById('faq-rows');
    var addButton = document.getElementById('add-faq-row');
    if (!container || !addButton) return;

    function bindRemove(row) {
        var remove = row.querySelector('.remove-faq-row');
        if (remove) {
            remove.addEventListener('click', function () {
                if (container.querySelectorAll('.faq-row').length > 1) {
                    row.parentNode.removeChild(row);
                } else {
                    var input = row.querySelector('input[name="faq_question[]"]');
                    var textarea = row.querySelector('textarea[name="faq_answer[]"]');
                    if (input) input.value = '';
                    if (textarea) textarea.value = '';
                }
            });
        }
    }

    Array.prototype.forEach.call(container.querySelectorAll('.faq-row'), bindRemove);

    addButton.addEventListener('click', function () {
        var index = container.querySelectorAll('.faq-row').length;
        var row = document.createElement('div');
        row.className = 'faq-row grid grid-cols-1 md:grid-cols-2 gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200';

        function field(tag, name, id, type, rows) {
            var wrap = document.createElement('div');
            var label = document.createElement('label');
            label.className = 'block text-xs font-medium text-gray-600 mb-1.5';
            label.setAttribute('for', id);
            label.textContent = tag === 'input' ? 'Question' : 'Answer';
            var el = document.createElement(tag);
            el.id = id;
            el.name = name;
            if (type) el.type = type; else el.rows = rows;
            el.className = 'w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary';
            wrap.appendChild(label);
            wrap.appendChild(el);
            return wrap;
        }

        row.appendChild(field('input', 'faq_question[]', 'faq_question_' + index, 'text'));
        row.appendChild(field('textarea', 'faq_answer[]', 'faq_answer_' + index, null, 3));

        var footer = document.createElement('div');
        footer.className = 'md:col-span-2 text-right';
        var remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'remove-faq-row text-xs text-red-600 hover:text-red-700 font-medium';
        remove.textContent = 'Remove';
        footer.appendChild(remove);
        row.appendChild(footer);

        container.appendChild(row);
        bindRemove(row);
    });
})();
</script>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left">
                <tr>
                    <th class="px-5 py-3 font-medium text-gray-600">Title</th>
                    <th class="px-5 py-3 font-medium text-gray-600">Category</th>
                    <th class="px-5 py-3 font-medium text-gray-600">Status</th>
                    <th class="px-5 py-3 font-medium text-gray-600">Views</th>
                    <th class="px-5 py-3 font-medium text-gray-600">Published</th>
                    <th class="px-5 py-3 font-medium text-gray-600 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!$guides): ?>
                <tr>
                    <td colspan="6" class="px-5 py-8 text-center text-gray-500">
                        No guides yet. Run <code class="text-xs">migration17.sql</code>, then open
                        <a href="/admin/seed-guides" class="text-primary hover:underline">/admin/seed-guides</a> to load the starter articles.
                    </td>
                </tr>
                <?php endif; ?>
                <?php foreach ($guides as $guide): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <div class="font-medium text-gray-800"><?= sanitize($guide['title']) ?></div>
                        <div class="text-xs text-gray-400 font-mono">/pages/guide-detail?slug=<?= sanitize($guide['slug']) ?></div>
                    </td>
                    <td class="px-5 py-3 text-gray-600"><?= sanitize($guide['category']) ?></td>
                    <td class="px-5 py-3">
                        <?php if ($guide['status'] === 'published'): ?>
                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Published</span>
                        <?php else: ?>
                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">Draft</span>
                        <?php endif; ?>
                        <?php if ((int)$guide['is_featured'] === 1): ?>
                        <span class="inline-flex items-center px-2 py-1 mt-1 text-xs font-medium rounded-full bg-orange-100 text-orange-700"><i class="fas fa-star mr-1"></i>Featured</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3 text-gray-600"><?= (int)$guide['views'] ?></td>
                    <td class="px-5 py-3 text-gray-500 text-xs">
                        <?= !empty($guide['published_at']) ? date('M d, Y', strtotime($guide['published_at'])) : '&mdash;' ?>
                    </td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end space-x-2">
                            <a href="/pages/guide-detail?slug=<?= urlencode($guide['slug']) ?>" target="_blank"
                                class="px-3 py-1.5 text-xs bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="/admin/guides?edit=<?= (int)$guide['id'] ?>"
                                class="px-3 py-1.5 text-xs bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= sanitize(csrf_token()) ?>">
                                <input type="hidden" name="toggle_status" value="1">
                                <input type="hidden" name="guide_id" value="<?= (int)$guide['id'] ?>">
                                <button type="submit" class="px-3 py-1.5 text-xs bg-purple-50 text-purple-600 rounded-lg hover:bg-purple-100 transition-colors"
                                    title="<?= $guide['status'] === 'published' ? 'Move to draft' : 'Publish' ?>">
                                    <i class="fas fa-<?= $guide['status'] === 'published' ? 'eye-slash' : 'check' ?>"></i>
                                </button>
                            </form>
                            <form method="POST" onsubmit="return confirm('Delete this guide permanently?')">
                                <input type="hidden" name="csrf_token" value="<?= sanitize(csrf_token()) ?>">
                                <input type="hidden" name="delete_guide" value="1">
                                <input type="hidden" name="guide_id" value="<?= (int)$guide['id'] ?>">
                                <button type="submit" class="px-3 py-1.5 text-xs bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
