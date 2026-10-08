<?php
/**
 * Second extended corpus: the interview topics that had a taxonomy entry but no
 * article.
 *
 * Kept separate from guides-content-extended.php purely for readability. Both
 * files are merged on slug by akazinet_seed_guides(), which is why no slug may
 * appear in more than one corpus file; tests/cases/08_guide_taxonomy.php fails
 * on a duplicate rather than letting the merge silently pick a winner.
 *
 * Category values must match a name in config/guide-taxonomy.php exactly. A
 * near-miss such as 'Education & Choosing a Course' written as 'Choosing a
 * Course' resolves to the wrong slug and puts the guide in the wrong filter.
 */

return [

[
    'title' => 'Applying for jobs in Rwanda: portals, tracking and following up',
    'slug' => 'applying-for-jobs-in-rwanda',
    'category' => 'Applying for Jobs',
    'excerpt' => 'Where applications actually go, how employers track them, what to do with online portals, when to follow up, and why sending one identical CV to fifty adverts works so badly.',
    'meta_title' => 'How to Apply for Jobs in Rwanda: Portals and Follow-ups',
    'meta_description' => 'How job applications work in Rwanda: where they go, how employers track them, how to handle online portals and written tests, when to follow up, and how to stay organised.',
    'is_featured' => false,
    'read_minutes' => 6,
    'content' => <<<'HTML'
<p>Applying for jobs feels like sending yourself out into an open space. It is not. For the employer, an application becomes a row in a tracking system, and everything you do before that row exists affects whether you become a person or a filename.</p>

<h2>Where applications actually go</h2>

<ul>
<li><strong>This site and comparable boards.</strong> Volume, low per-application effort, and a real chance of being seen.</li>
<li><strong>Company careers pages.</strong> Better odds, because you are applying to a specific employer. Check the careers page of every Kigali company you want to work for, even if they are not advertising on a board.</li>
<li><strong>Direct email to HR.</strong> Works for smaller employers. Short, specific, and addressed to a real person.</li>
<li><strong>Professional contacts on WhatsApp and LinkedIn.</strong> The highest-conversion route in Rwanda and the one most people underuse.</li>
<li><strong>Physical signboards and recruitment days.</strong> Still genuinely used for hospitality, retail, security and domestic roles, and for walk-in interviews at larger sites.</li>
<li><strong>Network referrals.</strong> Ask directly. A referral is not a shortcut around your CV, it is a reason for someone to read it.</li>
</ul>

<h2>One CV, many adverts: how to do this without it collapsing</h2>

<p>Sending an identical CV to fifty employers does not work, because recruiters can tell. The minimum that makes a batch of applications worth doing is:</p>

<ol>
<li><strong>Reorder the summary</strong> so the requirements the advert names come first.</li>
<li><strong>Reorder and rephrase experience</strong> so the bullet points nearest the top are the ones matching that advert.</li>
<li><strong>Change the filename</strong> and version marker for each employer.</li>
<li><strong>Rewrite the cover note each time.</strong> This is the part that carries the weight, and it is four sentences, not a template.</li>
</ol>

<p>Forty-five minutes of tailoring beats ten untailored applications in almost every case.</p>

<h2>Online portals</h2>

<p>Portals are widely disliked and often unavoidable. Practical points: fill every field, because an incomplete form is often filtered before a human sees it. Upload the CV as PDF unless the portal states otherwise. Save your answers somewhere, because many portals time out. Create one account and reuse it rather than registering repeatedly, which can create duplicate applications that confuse the employer.</p>

<p>If a portal asks for a national ID number at application stage, that is normal on larger employers' systems. It is a signal to slow down and verify the employer is real, not a reason to abandon a legitimate application.</p>

<h2>Tracking your applications</h2>

<p>A simple spreadsheet with columns for employer, role, date applied, channel, contact name, follow-up date and status is enough. Without it, applications blur together and you will follow up on the wrong ones. Set the follow-up date when you apply, not when you remember.</p>

<h2>Following up</h2>

<p>One polite follow-up, roughly one week after applying. Not three, and not a chase. It is a reason for them to look at you again, so give them one: mention something specific about the role, or offer to send anything they asked for and did not receive.</p>

<p>After a follow-up with no reply, close the application and move on. Chasing further only damages you.</p>

<h2>After an interview</h2>

<p>Thank them the same day, referencing something specific from the discussion. Then one follow-up a week later if you have heard nothing. That is the full expected effort.</p>
HTML,
    'faq' => [
        [
            'question' => 'How many jobs should I apply to in Rwanda?',
            'answer' => 'Fewer, tailored applications beat many untailored ones. Forty-five minutes reordering your CV and rewriting four sentences of cover note per employer is a better use of a day than sending an identical CV to fifty adverts, because recruiters can tell the difference and identical submissions read as mass application.',
        ],
        [
            'question' => 'Should I follow up after applying for a job?',
            'answer' => 'Yes, once, roughly a week after applying, and make it useful: mention something specific about the role, or offer to resend something they asked for and did not receive. After that single follow-up with no reply, close the application. Repeated chasing damages you rather than helping.',
        ],
        [
            'question' => 'Do online job portals in Rwanda work?',
            'answer' => 'They are widely disliked and often unavoidable. Fill in every field, because incomplete forms are frequently filtered before a human sees them. Upload a PDF unless stated otherwise, save your answers in case the portal times out, and use one account rather than registering repeatedly, which creates duplicate applications that confuse the employer.',
        ],
        [
            'question' => 'Do I need to track my job applications?',
            'answer' => 'Yes, with a simple spreadsheet: employer, role, date applied, channel, contact name, follow-up date and status. Without it, applications blur together and you will follow up on the wrong ones. Set the follow-up date at the moment you apply rather than when you remember.',
        ],
    ],
    'sources' => [],
],

[
    'title' => 'The first interview: a full preparation checklist for Rwanda',
    'slug' => 'first-interview-preparation-checklist',
    'category' => 'Interviews',
    'excerpt' => 'A step-by-step preparation routine for your first interview, from confirming the format to the day itself, plus what to do about nerves and what to ask when they say "do you have any questions".',
    'meta_title' => 'First Job Interview Preparation Checklist for Rwanda',
    'meta_description' => 'A practical first-interview checklist for Rwanda: how to prepare, what to bring, how to handle nerves, how to answer "do you have any questions", and what to do after.',
    'is_featured' => false,
    'read_minutes' => 7,
    'content' => <<<'HTML'
<p>Preparation is mostly about removing surprises. Every question below has been asked in a Rwandan hiring process, and none of them require experience to answer well.</p>

<h2>The week before</h2>

<ul>
<li><strong>Research the employer.</strong> What they do, who leads them, what clients or sectors they serve, what has happened recently, and what you can genuinely comment on. Noticing something specific about their work is the cheapest way to sound prepared.</li>
<li><strong>Re-read the advert line by line.</strong> Every requirement listed is a likely question. If the advert says "strong Excel", assume they will ask what you have done in Excel.</li>
<li><strong>Prepare three stories.</strong> One problem you solved, one time you worked under pressure, one mistake and what you did about it. Write them out in full so you can retell them in two minutes without sounding rehearsed.</li>
<li><strong>Prepare two questions to ask them.</strong> You will be asked. Coming with none is the most common avoidable mistake.</li>
<li><strong>Sort out logistics.</strong> Route, time, parking, who to ask for, what to carry. Arriving ten minutes early is not optional.</li>
</ul>

<h2>What to bring</h2>

<p>Two printed CVs, in case they need to look at one while you talk. Your national ID. Copies of your diploma and any certificates listed in your CV. A notebook and two pens, which signal that you take notes seriously. A charged phone, but silent.</p>

<p>Bring the documents they asked for. Bringing extra unsolicited personal documents, especially an ID copy or bank details, is not a strength. See the guide on fake job adverts for why.</p>

<h2>On the day</h2>

<ul>
<li>Arrive ten to fifteen minutes early. Early reads as reliable.</li>
<li>Turn your phone fully off, not just on silent.</li>
<li>When greeted, greet back, offer a handshake if offered, and use a clear, audible voice.</li>
<li>Sit when invited. Keep your phone and bag on your lap or on the floor beside you, never on the table.</li>
</ul>

<h2>Handling nerves</h2>

<p>Nerves are normal and interviewers in Rwanda know you are nervous. The things that make it worse are avoidable: arriving late, not knowing where you are, and having no idea what the company does. Preparation fixes all three.</p>

<p>Physically: slow your breathing before you go in, four seconds in, six seconds out, a few times. Sit with both feet on the floor. Hold something, a pen or a notebook. If you blank, say so: <em>"Let me think for a moment"</em> is a completely acceptable thing to say and is far better than filling the silence with filler.</p>

<p>If your Kinyarwanda is stronger than your English, it is completely normal and entirely acceptable to ask the interviewer to conduct the interview in Kinyarwanda, or to switch. Many interviews start in one language and move to the other.</p>

<h2>Questions to ask them</h2>

<p>Pick two or three, and make them specific to the role:</p>

<ul>
<li>What does success look like in the first three months for this role?</li>
<li>How is performance measured, and how often?</li>
<li>What is the team structure, and who would I work with most directly?</li>
<li>What has changed about this role in the last year?</li>
<li>Is there a probation period, and what does it involve?</li>
</ul>

<h2>After the interview</h2>

<p>Send a short thank-you message the same day, referencing something specific from the discussion. It is a small differentiator and most candidates do not do it.</p>

<p>Then follow up once, around one week later, if you have heard nothing. One message, polite, no follow-up if there is no reply. After that, move on.</p>
HTML,
    'faq' => [
        [
            'question' => 'What should I bring to a job interview in Rwanda?',
            'answer' => 'Two printed CVs, your national ID, copies of the diploma and certificates you listed, a notebook and two pens, and a charged phone set to fully off. Bring the documents the employer asked for. Avoid bringing unsolicited personal documents such as an ID copy or bank details before an offer exists.',
        ],
        [
            'question' => 'Can I do a job interview in Kinyarwanda if my English is weaker?',
            'answer' => 'Yes, and you should. Ask the interviewer at the start which language they would prefer, and switch if needed. Many interviews in Rwanda begin in one language and move to the other. Note that many interviews deliberately test written or professional English, so if you claim fluent English, be accurate about your level.',
        ],
        [
            'question' => 'What if I have nothing to ask at the end of an interview?',
            'answer' => 'Prepare two or three specific questions in advance, such as what success looks like in the first three months, how performance is measured, or the team structure. Arriving with no questions is the most common avoidable mistake, and it suggests you did not prepare or are not interested.',
        ],
        [
            'question' => 'What if I do not know an answer in an interview?',
            'answer' => 'Say so plainly and explain how you would find out. "I have not worked with that system, but I have learned tools quickly before and would pick it up within the first week" is a strong answer. Inventing an answer that collapses under a follow-up question is much weaker than admitting a gap and showing how you close it.',
        ],
        [
            'question' => 'Should I send a thank-you message after an interview in Rwanda?',
            'answer' => 'Yes, a short message the same day referencing something specific from the discussion. It is a small differentiator and most candidates skip it. Keep it to a few lines, and send it to the address the interviewer gives you or the one on the advert.',
        ],
    ],
    'sources' => [],
],

[
    'title' => 'Online tests and assessments in Rwandan hiring',
    'slug' => 'online-tests-and-assessments-rwanda',
    'category' => 'Interviews',
    'excerpt' => 'CV screening is only the first stage. How aptitude tests, personality questionnaires, Excel and typing tests, and take-home assignments work, what is being measured, and how to prepare without gaming them.',
    'meta_title' => 'Online Tests and Assessments in Rwandan Hiring',
    'meta_description' => 'How aptitude tests, personality questionnaires, Excel and typing tests and take-home assignments work in Rwandan hiring, what is measured, and how to prepare honestly.',
    'is_featured' => false,
    'read_minutes' => 6,
    'content' => <<<'HTML'
<p>For graduate and entry-level roles in Rwanda, especially at banks, telecom operators, BPOs and larger employers, there is often a stage between the CV and the interview. It is commonly dismissed as a formality. It is not.</p>

<h2>The stages you are most likely to meet</h2>

<ul>
<li><strong>Aptitude or numerical reasoning.</strong> Arithmetic, percentages, ratios, data interpretation, sometimes verbal reasoning. Frequently timed. The most common format in banking and telecom graduate schemes.</li>
<li><strong>Personality or behavioural questionnaire.</strong> Forced-choice items describing how you tend to behave at work. Rarely has a right answer. Assesses consistency and, in some employers, team fit.</li>
<li><strong>Skills tests.</strong> Excel, bookkeeping, typing speed, data entry, a language proficiency check, or a short practical in your field.</li>
<li><strong>Take-home assignment.</strong> A short piece of work, sometimes a case, sometimes writing, occasionally a small spreadsheet analysis. Longer deadlines, sometimes a few days.</li>
<li><strong>Group or panel interview.</strong> Several candidates interviewed together, often observed by senior staff who never ask a question.</li>
</ul>

<h2>How to prepare honestly</h2>

<p>For numerical and aptitude tests, the only real preparation is practice at speed under time pressure. Free practice tests exist for common published formats. Do the arithmetic without a calculator for the first few, then with, because the test may not allow one.</p>

<p>For Excel and skills tests, use the actual software. A large share of failed Excel tests are avoidable by people who genuinely know the job but use Excel rarely. Know the shortcuts you would use in a real job: lookup functions, pivot tables, conditional formatting, cleaning messy data, date arithmetic.</p>

<p>For personality questionnaires, do not try to answer "correctly". They are usually scored for internal consistency, and attempting to look ideal across hundreds of items produces an inconsistent profile that gets flagged. Answer honestly at a considered pace.</p>

<p>For take-home assignments, treat them as real work: check the brief twice, answer what was asked rather than what is interesting, follow the requested format, name the file clearly, and submit before the deadline with a margin. Give yourself a personal deadline earlier than the real one.</p>

<h2>What is being measured, and what is not</h2>

<p>Aptitude tests measure whether you can do the numerical or analytical work the role involves. They are often weighted more heavily than the interview in graduate schemes, and a poor score can end the process regardless of a good CV.</p>

<p>Personality questionnaires do not measure your worth or whether you are a good person. They are used to see whether your working style resembles what the role needs.</p>

<h2>Practical points</h2>

<ul>
<li>Check you know how to join: which link, which platform, whether you need to install anything, whether a webcam is required.</li>
<li>Test your connection and camera the day before, not an hour before.</li>
<li>Know the time limit and how many attempts are permitted, if stated.</li>
<li>Find out whether the test is scored or purely practice.</li>
<li>If a test invitation arrives from an address you cannot verify, do not enter personal details. See the guide on fake job adverts.</li>
</ul>

<p>None of this is difficult. It is just unfamiliar to most candidates, and familiarity is the whole advantage.</p>
HTML,
    'faq' => [
        [
            'question' => 'Do I need to prepare for online tests in Rwanda?',
            'answer' => 'Yes. Banks, telecom operators, BPOs and larger employers commonly use aptitude or numerical tests as a weighted stage before or alongside the interview. The practical preparation is practice at speed under time pressure, using free practice tests for the published format.',
        ],
        [
            'question' => 'Are there right answers in a personality questionnaire?',
            'answer' => 'Rarely. Forced-choice items are usually scored for internal consistency and compared against the profile of successful people in that role. Do not attempt to answer ideally across hundreds of items, because an inconsistent profile tends to get flagged. Answer honestly at a considered pace.',
        ],
        [
            'question' => 'How should I prepare for an Excel test?',
            'answer' => 'Use the actual software regularly rather than memorising shortcut lists. Practise the tasks that appear in real job adverts and tests: lookup functions, pivot tables, conditional formatting, cleaning inconsistent data, and date and number formatting.',
        ],
        [
            'question' => 'How long do I get for a take-home assignment?',
            'answer' => 'Usually a few days. Set your own deadline earlier than the real one, because most assignments are not marked down for submitting slightly early. Check the brief twice, answer exactly what was asked, follow the requested format, name the file clearly, and submit with enough margin for the portal or email to work.',
        ],
    ],
    'sources' => [],
],

];
