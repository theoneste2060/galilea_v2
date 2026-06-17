<?php
/**
 * One-time, idempotent updater that applies Galilea's authoritative company
 * data (from the RDB certificate, company profile and GBP brief) to a LIVE
 * database — without dropping anything else.
 *
 * Run on the server from the project root:
 *     php tools/apply_authoritative_content.php
 *
 * Safe to run more than once. It UPSERTS settings, the About page, the FAQ set,
 * leadership team members, and the two extra services. It never deletes rows.
 */
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

$pdo = Database::pdo();
$changed = [];
function note(string $s): void { global $changed; $changed[] = $s; echo "  • $s\n"; }

echo "Applying authoritative content…\n";

/* ── 1. Settings (update value if present, else insert) ─────────────────── */
$settings = [
    ['address_kigali', 'Kigali Address', 'F1-8B Unify Building, Inyarurembo, Kiyovu, Nyarugenge, Kigali', 'contact'],
    ['business_hours', 'Business Hours', 'Mon–Fri: 8:00 AM – 6:00 PM · Sat: 9:00 AM – 1:00 PM · Sun: Closed', 'contact'],
    ['site_url', 'Canonical Site URL (e.g. https://galileagloballogistics.rw)', 'https://galileagloballogistics.rw', 'seo'],
    ['org_legal_name', 'Legal Organization Name', 'Galilea Global Logistics LTD', 'seo'],
    ['company_reg', 'Company Registration / TIN', 'TIN 155855944 · Registered in Rwanda (RDB)', 'seo'],
];
$upd = $pdo->prepare("UPDATE site_settings SET value = ?, updated_at = datetime('now') WHERE key = ?");
$ins = $pdo->prepare('INSERT INTO site_settings (key, label, value, group_name) VALUES (?,?,?,?)');
$has = $pdo->prepare('SELECT COUNT(*) FROM site_settings WHERE key = ?');
foreach ($settings as [$k, $label, $val, $grp]) {
    $has->execute([$k]);
    if ((int) $has->fetchColumn() > 0) { $upd->execute([$val, $k]); note("setting updated: $k"); }
    else { $ins->execute([$k, $label, $val, $grp]); note("setting added: $k"); }
}

/* ── 2. About page (overwrite body with the authoritative copy) ─────────── */
$aboutBody = '<p>Galilea Global Logistics LTD is a premier multimodal logistics provider specialising in end-to-end supply chain management, headquartered in Kigali, Rwanda. With infrastructure spanning sea, air and land, we operate at the intersection of traditional reliability and modern, technology-driven innovation.</p>'
    . '<p>We don\'t just transport cargo; we manage the heartbeat of your business. From the first mile of manufacturing to the final mile of delivery, our team ensures your products navigate the global marketplace with precision and speed.</p>'
    . '<h2>Our Vision</h2><p>To be the most reliable bridge between global markets, empowering businesses through seamless, technology-driven supply chain solutions.</p>'
    . '<h2>Our Mission</h2><p>To simplify the complexities of international trade by providing transparent, efficient and scalable logistics services that guarantee “Trusted Trade and Global Reach.”</p>'
    . '<h2>Who we are</h2><p>Founded to solve the real frictions of trading between Africa and Asia, Galilea pairs local knowledge of East African corridors and customs with a permanent presence in the manufacturing heartlands of South China. From our offices in Kigali, Guangzhou and Yiwu, we manage shipments end to end so our clients can buy, sell and grow with confidence.</p>'
    . '<h2>Why partner with Galilea</h2><ul>'
    . '<li><strong>Global network</strong> — access to every major trade hub via sea, air and land, across 130+ countries.</li>'
    . '<li><strong>Operational transparency</strong> — real-time tracking and clear communication at every milestone.</li>'
    . '<li><strong>Scalability</strong> — solutions that grow with your business, from single pallets to massive project cargo.</li>'
    . '<li><strong>Market expertise</strong> — deep local knowledge of the African and Chinese trade landscape.</li>'
    . '</ul>'
    . '<h2>Our promise</h2><p>“In an unpredictable world, your supply chain shouldn\'t be a gamble. We provide the stability, technology and Trusted Trade your brand deserves.” Every shipment is tracked, every duty is transparent, and every client has a named point of contact.</p>';
if ((int) Database::value("SELECT COUNT(*) FROM pages WHERE slug = 'about'") > 0) {
    $pdo->prepare("UPDATE pages SET body = ?, updated_at = datetime('now') WHERE slug = 'about'")->execute([$aboutBody]);
    note('About page updated');
}

/* ── 3. FAQs (upsert by question text) ──────────────────────────────────── */
$faqs = [
    ['What shipping services does Galilea Global Logistics offer?', 'Galilea offers sea cargo and ocean freight (FCL & LCL), air freight, road and land transport, warehousing and distribution, customs clearance and declaration, and China business connection including verified supplier sourcing and money transfers. We operate across 130+ countries with offices in Kigali, Guangzhou and Yiwu.', 1],
    ['How do I track my shipment?', 'Enter your container number, booking reference, or bill of lading on our Track & Trace page to see live status and milestones. You can also call our support line at +250 788 229 632 for real-time updates.', 2],
    ['Does Galilea handle customs clearance and dangerous goods?', 'Yes. We provide full customs clearance, maritime declaration, dangerous-goods declaration and duty management. Our compliance team ensures smooth border crossings for all cargo types, including pharmaceuticals and oversized equipment.', 3],
    ['Can Galilea help me source products from China?', 'Absolutely. Through our Guangzhou and Yiwu offices we connect you with verified suppliers, arrange factory visits, and provide money-transfer support — your direct link to Chinese manufacturers.', 4],
    ['What is the typical transit time from China to Rwanda?', 'Ocean freight from Guangzhou to Kigali typically takes 25–35 days (FCL) or 30–40 days (LCL). Air freight takes 3–7 days depending on the route. Contact us for a specific quote based on your shipment details.', 5],
    ['How do I request a freight quote?', 'Use the Get a Quote form on our Contact page, email info@galileagloballogistics.rw, or call +250 788 229 632. Our team typically responds within one business day with a tailored quote.', 6],
    ['Does Galilea handle oversized or project cargo?', 'Yes. We specialise in project cargo — oversized, heavy or high-value equipment that requires custom engineering, routing and permits. We have moved machinery from China to Rwanda, including permits and specialised trucking.', 7],
    ['What countries and regions does Galilea serve?', 'Galilea operates across 130+ countries with a focus on the China–East Africa corridor. We have offices in Kigali (Rwanda), Guangzhou and Yiwu (China), with partners across Europe, the Middle East and Africa.', 8],
    ['Does Galilea offer warehousing services?', 'Yes. We operate secure warehousing in Kigali, Guangzhou and Yiwu with inventory-management systems, and we provide e-commerce fulfilment including pick-and-pack and dispatch.', 9],
    ['What makes Galilea different from other logistics companies in Rwanda?', 'Three things: (1) physical offices in Kigali, Guangzhou and Yiwu giving you direct access to Chinese suppliers; (2) 24/7 operations support with a 99% on-time delivery rate; and (3) genuine end-to-end service — sea, air, land, customs, warehousing, sourcing and financial support — all under one roof.', 10],
];
$faqHas = $pdo->prepare('SELECT id FROM faqs WHERE question = ?');
$faqUpd = $pdo->prepare("UPDATE faqs SET answer = ?, sort_order = ? WHERE id = ?");
$faqIns = $pdo->prepare('INSERT INTO faqs (question, answer, sort_order) VALUES (?,?,?)');
foreach ($faqs as [$q, $a, $o]) {
    $faqHas->execute([$q]);
    $id = $faqHas->fetchColumn();
    if ($id) { $faqUpd->execute([$a, $o, $id]); } else { $faqIns->execute([$q, $a, $o]); note("FAQ added: " . mb_strimwidth($q, 0, 40, '…')); }
}

/* ── 4. Leadership team (upsert by name) ────────────────────────────────── */
$team = [
    ['Jean Damascene ITANGIGABANYA', 'Managing Director', 'Leads Galilea\'s global operations, strategy and partnerships across the China–East Africa corridor.', '+250 788 229 632', 'info@galileagloballogistics.rw', 1],
    ['Jean Paul MULIGANDE', 'Chairperson of the Board', 'Provides governance and strategic direction for Galilea Global Logistics.', '', 'info@galileagloballogistics.rw', 2],
    ['Jules BAWESHEMA', 'Sales & Marketing Manager', 'Drives client relationships and growth across East Africa and Asia.', '+250 785 476 239', 'info@galileagloballogistics.rw', 3],
];
$tmHas = $pdo->prepare('SELECT id FROM team_members WHERE full_name = ?');
$tmUpd = $pdo->prepare('UPDATE team_members SET role = ?, bio = ?, phone = ?, email = ?, sort_order = ? WHERE id = ?');
$tmIns = $pdo->prepare('INSERT INTO team_members (full_name, role, bio, phone, email, sort_order) VALUES (?,?,?,?,?,?)');
foreach ($team as [$n, $r, $b, $p, $e, $o]) {
    $tmHas->execute([$n]);
    $id = $tmHas->fetchColumn();
    if ($id) { $tmUpd->execute([$r, $b, $p, $e, $o, $id]); } else { $tmIns->execute([$n, $r, $b, $p, $e, $o]); note("team member added: $n"); }
}

/* ── 5. Extra services (insert if the slug is missing) ──────────────────── */
$services = [
    ['Project Cargo', 'project-cargo', 'Specialised handling of oversized, heavy and high-value equipment — engineering, routing and permits.',
     '<p>Some shipments do not fit in a container or a standard process. Project cargo — industrial machinery, construction plant, generators, vehicles and other out-of-gauge or high-value equipment — demands route surveys, lifting plans, special permits and careful coordination. Galilea has the experience to move it safely from origin to site.</p><h3>How we handle project cargo</h3><ul><li><strong>Engineering &amp; route survey</strong> — assessing weights, dimensions and the road, port and bridge constraints along the way.</li><li><strong>Specialised equipment</strong> — flatbeds, low-loaders, cranes and lifting gear matched to the cargo.</li><li><strong>Permits &amp; escorts</strong> — abnormal-load permits and convoy arrangements for cross-border moves.</li><li><strong>End-to-end coordination</strong> — a single project lead from collection in China to delivery on site in Rwanda or the region.</li></ul><h3>Built for the difficult moves</h3><p>We have delivered oversized machinery from China to project sites across Rwanda, handling the permits, specialised trucking and on-site delivery from start to finish.</p>',
     null, 0, 7],
    ['E-commerce Fulfilment', 'ecommerce-fulfilment', 'Scalable storage, pick-and-pack and dispatch for online retailers — grow without the growing pains.',
     '<p>Selling online means your logistics has to keep pace with your orders — every day, without errors. Galilea offers scalable fulfilment so you can hold stock close to your customers, ship the moment an order comes in, and scale up smoothly as you grow.</p><h3>What our fulfilment covers</h3><ul><li><strong>Receiving &amp; storage</strong> — your inventory held securely in our Kigali and China facilities.</li><li><strong>Pick, pack &amp; dispatch</strong> — accurate order fulfilment with same- or next-day handling.</li><li><strong>Inventory visibility</strong> — clear stock records and goods-in/goods-out reporting.</li><li><strong>Returns handling</strong> — a simple process for receiving and restocking returns.</li></ul><h3>Scale without the headache</h3><p>One of our clients grew from 200 to over 2,000 orders a month without missing a beat, because sourcing, warehousing and last-mile delivery all ran through one partner.</p>',
     null, 0, 8],
];
$svcHas = $pdo->prepare('SELECT COUNT(*) FROM services WHERE slug = ?');
$svcIns = $pdo->prepare('INSERT INTO services (title, slug, short_description, description, image_path, featured, sort_order) VALUES (?,?,?,?,?,?,?)');
foreach ($services as $s) {
    $svcHas->execute([$s[1]]);
    if ((int) $svcHas->fetchColumn() === 0) { $svcIns->execute($s); note("service added: {$s[0]}"); }
}

echo "\nDone. " . count($changed) . " change(s) applied.\n";
if (function_exists('log_activity')) { /* CLI: no admin session, skip */ }
