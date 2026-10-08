/**
 * AkaziNet - Office location map (contact page)
 *
 * Leaflet is loaded from a CDN by the page itself and only exists on the contact
 * page, so both the library and the mount point are treated as optional. The
 * centre point comes from data attributes rendered by PHP, which keeps the
 * coordinates in config/database.php rather than duplicated here.
 */

(function () {
    'use strict';

    function init() {
        var container = document.getElementById('contact-map');
        if (!container || typeof L === 'undefined') return;

        var lat = parseFloat(container.getAttribute('data-lat'));
        var lng = parseFloat(container.getAttribute('data-lng'));
        var label = container.getAttribute('data-label') || '';

        // A missing or malformed data attribute must not throw: it would break
        // the rest of the page's script for a map nobody came to see.
        if (isNaN(lat) || isNaN(lng)) return;

        var map = L.map(container).setView([lat, lng], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            // Required by the OpenStreetMap tile usage policy, and it is also
            // the only credit they ask for.
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // A divIcon draws the pin with CSS instead of an image. Leaflet's default
        // marker resolves its sprite relative to the stylesheet, which is broken
        // when the library is served from a CDN, so this avoids the missing-image
        // icon that a CDN-hosted Leaflet usually shows.
        var pin = L.divIcon({
            className: '',
            html: '<span style="display:block;width:20px;height:20px;border-radius:9999px;background:#f97316;border:3px solid #ffffff;box-shadow:0 1px 4px rgba(0,0,0,0.4)"></span>',
            iconSize: [20, 20],
            iconAnchor: [10, 10]
        });

        var popup = document.createElement('div');
        popup.textContent = label;

        L.marker([lat, lng], { icon: pin, title: label, alt: label })
            .addTo(map)
            .bindPopup(popup);

        // Wheel zoom stays off until the map has been clicked. Left on, the map
        // swallows page scrolling as soon as the cursor crosses it, which on a
        // long contact page is most of the page.
        map.on('click', function () {
            map.scrollWheelZoom.enable();
        });
        map.on('mouseout', function () {
            map.scrollWheelZoom.disable();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
