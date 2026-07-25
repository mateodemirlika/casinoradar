<?php
/**
 * One-time: adds Terms of Use, Cookies Policy, and Imprint pages (new),
 * and replaces the placeholder Privacy Policy content with a real policy —
 * in EN/DE/ZH, modeled on Velaro Ads' existing legal pages for its other
 * sites. Run via `wp eval-file wp-cli/add-legal-pages.php`.
 *
 * Two passes: pass 1 creates/updates the pages so every permalink exists;
 * pass 2 fills in the real content, since several pages cross-link each
 * other (Terms -> Contact, Privacy -> Cookies Policy, etc.) and those links
 * need the correct per-language URL, not a hardcoded guess.
 */

defined( 'ABSPATH' ) || exit;

function ww_legal_seed_lang( int $post_id, string $lang ): void {
	if ( function_exists( 'pll_set_post_language' ) ) {
		pll_set_post_language( $post_id, $lang );
	}
}

function ww_legal_upsert_page( string $title, string $slug, string $lang ): int {
	$existing = get_posts( array( 'post_type' => 'page', 'name' => $slug, 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids' ) );
	if ( ! empty( $existing ) ) {
		return (int) $existing[0];
	}
	$id = wp_insert_post( array(
		'post_type'    => 'page',
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_status'  => 'publish',
		'post_content' => '', // filled in pass 2
	) );
	if ( is_wp_error( $id ) || ! $id ) {
		WP_CLI::warning( "Failed to create page '{$title}'" );
		return 0;
	}
	ww_legal_seed_lang( $id, $lang );
	return (int) $id;
}

// -----------------------------------------------------------------------------
// Pass 1 — ensure every page exists (new ones empty, Privacy Policy as-is).
// -----------------------------------------------------------------------------

$ids = array();

$ids['terms']['en'] = ww_legal_upsert_page( 'Terms of Use', 'terms-of-use', 'en' );
$ids['terms']['de'] = ww_legal_upsert_page( 'Nutzungsbedingungen', 'nutzungsbedingungen', 'de' );
$ids['terms']['zh'] = ww_legal_upsert_page( '使用条款', sanitize_title( '使用条款' ), 'zh' );

$ids['cookies']['en'] = ww_legal_upsert_page( 'Cookies Policy', 'cookies-policy', 'en' );
$ids['cookies']['de'] = ww_legal_upsert_page( 'Cookie-Richtlinie', 'cookie-richtlinie', 'de' );
$ids['cookies']['zh'] = ww_legal_upsert_page( 'Cookie政策', sanitize_title( 'Cookie政策' ), 'zh' );

$ids['imprint']['en'] = ww_legal_upsert_page( 'Imprint', 'imprint', 'en' );
$ids['imprint']['de'] = ww_legal_upsert_page( 'Impressum', 'impressum', 'de' );
$ids['imprint']['zh'] = ww_legal_upsert_page( '公司信息', sanitize_title( '公司信息' ), 'zh' );

foreach ( $ids as $group ) {
	if ( function_exists( 'pll_save_post_translations' ) && count( array_filter( $group ) ) > 1 ) {
		pll_save_post_translations( $group );
	}
}

$privacy_page = get_page_by_path( 'privacy-policy' );
$ids['privacy']['en'] = $privacy_page ? $privacy_page->ID : 0;
$ids['privacy']['de'] = function_exists( 'pll_get_post' ) ? pll_get_post( $ids['privacy']['en'], 'de' ) : 0;
$ids['privacy']['zh'] = function_exists( 'pll_get_post' ) ? pll_get_post( $ids['privacy']['en'], 'zh' ) : 0;

$contact = get_page_by_path( 'contact' );
$ids['contact']['en'] = $contact ? $contact->ID : 0;
$ids['contact']['de'] = function_exists( 'pll_get_post' ) ? pll_get_post( $ids['contact']['en'], 'de' ) : 0;
$ids['contact']['zh'] = function_exists( 'pll_get_post' ) ? pll_get_post( $ids['contact']['en'], 'zh' ) : 0;

// Permalinks for cross-linking, per language.
$url = array();
foreach ( array( 'terms', 'cookies', 'imprint', 'privacy', 'contact' ) as $key ) {
	foreach ( array( 'en', 'de', 'zh' ) as $lang ) {
		$pid = $ids[ $key ][ $lang ] ?? 0;
		$url[ $key ][ $lang ] = $pid ? get_permalink( $pid ) : '';
	}
}

// -----------------------------------------------------------------------------
// Pass 2 — real content, per language.
// -----------------------------------------------------------------------------

$terms_content = array(
	'en' => <<<HTML
<!-- wp:heading --><h2>1. Agreement to Terms</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>By accessing or using CasinoRadar (the "Site"), you agree to be bound by these Terms of Use. If you do not agree, please do not use the Site. We may update these Terms from time to time; continued use of the Site after changes are posted constitutes your acceptance of the revised Terms.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>2. Content &amp; Intellectual Property</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>All text, graphics, logos, and software on the Site are owned by or licensed to Velaro Ads and are protected by applicable copyright and trademark laws. You may not reproduce, distribute, or create derivative works from any part of the Site without our prior written permission.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>3. Permitted Use</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>The Site is provided for your personal, non-commercial use. You may view and share individual pages for informational purposes, provided you do not remove any copyright or attribution notices.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>4. Prohibited Activities</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>You agree not to: use the Site for any unlawful purpose; attempt to interfere with the Site's security or normal operation; use bots, scrapers, or other automated tools to access or extract content; or copy, resell, or otherwise commercially exploit any part of the Site without our written consent.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>5. Account Security</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>If the Site offers account features (such as newsletter subscriptions or comment submissions), you are responsible for maintaining the confidentiality of any credentials associated with your account and for notifying us promptly of any unauthorized use.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>6. Affiliate Disclosure</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>CasinoRadar is an affiliate marketing website. We may earn a commission when you sign up with or deposit at an online casino through links on this Site. This commission never influences the independence or objectivity of our reviews, ratings, or rankings — every operator is evaluated using the same published methodology regardless of any commercial relationship.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>7. Third-Party Links</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>The Site contains links to third-party websites, including the online casinos we review. We are not responsible for the content, terms, or privacy practices of any third-party site, and including a link does not constitute an endorsement of that operator's services.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>8. Disclaimers</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>The Site and its content are provided "as is" and "as available" without warranties of any kind, express or implied. We do not guarantee that the information on the Site is complete, accurate, or up to date, and we recommend always confirming a casino's current terms directly with the operator before depositing.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>9. Limitation of Liability</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>To the fullest extent permitted by law, Velaro Ads shall not be liable for any indirect, incidental, or consequential damages — including loss of funds gambled with a third-party operator — arising from your use of the Site or reliance on its content.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>10. Termination</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>We may suspend or restrict your access to the Site at any time, without notice, if we believe you have violated these Terms.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>11. Governing Law</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>These Terms are governed by the laws of Albania, without regard to its conflict-of-law principles. Any disputes arising from these Terms shall be subject to the exclusive jurisdiction of the competent courts of Albania.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>12. Contact</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Questions about these Terms of Use can be sent to us via our <a href="{$url['contact']['en']}">Contact page</a>.</p><!-- /wp:paragraph -->
HTML,
	'de' => <<<HTML
<!-- wp:heading --><h2>1. Zustimmung zu den Bedingungen</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Durch den Zugriff auf oder die Nutzung von CasinoRadar (die "Website") erklären Sie sich mit diesen Nutzungsbedingungen einverstanden. Wenn Sie nicht einverstanden sind, nutzen Sie die Website bitte nicht. Wir können diese Bedingungen von Zeit zu Zeit aktualisieren; die fortgesetzte Nutzung der Website nach Veröffentlichung von Änderungen gilt als Ihre Zustimmung zu den überarbeiteten Bedingungen.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>2. Inhalte &amp; Geistiges Eigentum</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Alle Texte, Grafiken, Logos und Software auf der Website sind Eigentum von Velaro Ads oder an Velaro Ads lizenziert und durch geltendes Urheber- und Markenrecht geschützt. Sie dürfen keinen Teil der Website ohne unsere vorherige schriftliche Zustimmung reproduzieren, verbreiten oder daraus abgeleitete Werke erstellen.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>3. Erlaubte Nutzung</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Die Website ist für Ihre persönliche, nicht-kommerzielle Nutzung bestimmt. Sie dürfen einzelne Seiten zu Informationszwecken ansehen und teilen, sofern Sie keine Urheberrechts- oder Quellenangaben entfernen.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>4. Verbotene Aktivitäten</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Sie verpflichten sich, die Website nicht für rechtswidrige Zwecke zu nutzen, nicht zu versuchen, die Sicherheit oder den normalen Betrieb der Website zu beeinträchtigen, keine Bots, Scraper oder andere automatisierte Tools zum Zugriff auf oder zur Extraktion von Inhalten zu verwenden und keinen Teil der Website ohne unsere schriftliche Zustimmung zu kopieren, weiterzuverkaufen oder kommerziell zu nutzen.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>5. Kontosicherheit</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Falls die Website Kontofunktionen anbietet (z. B. Newsletter-Abonnements oder Kommentarfunktionen), sind Sie für die Vertraulichkeit der mit Ihrem Konto verbundenen Zugangsdaten verantwortlich und müssen uns umgehend über jede unbefugte Nutzung informieren.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>6. Partnerprogramm-Offenlegung</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>CasinoRadar ist eine Affiliate-Marketing-Website. Wir erhalten möglicherweise eine Provision, wenn Sie sich über Links auf dieser Website bei einem Online-Casino anmelden oder eine Einzahlung tätigen. Diese Provision beeinflusst niemals die Unabhängigkeit oder Objektivität unserer Testberichte, Bewertungen oder Rankings — jeder Anbieter wird nach derselben veröffentlichten Methodik bewertet, unabhängig von einer geschäftlichen Beziehung.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>7. Links zu Drittanbietern</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Die Website enthält Links zu Websites Dritter, einschließlich der von uns bewerteten Online-Casinos. Wir sind nicht verantwortlich für die Inhalte, Bedingungen oder Datenschutzpraktiken von Websites Dritter, und das Einbinden eines Links stellt keine Empfehlung des jeweiligen Anbieters dar.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>8. Haftungsausschluss</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Die Website und ihre Inhalte werden "wie besehen" und "wie verfügbar" ohne jegliche ausdrückliche oder stillschweigende Gewährleistung bereitgestellt. Wir garantieren nicht, dass die Informationen auf der Website vollständig, korrekt oder aktuell sind, und empfehlen, die aktuellen Bedingungen eines Casinos stets direkt beim Anbieter zu überprüfen, bevor Sie eine Einzahlung tätigen.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>9. Haftungsbeschränkung</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Im gesetzlich zulässigen Umfang haftet Velaro Ads nicht für indirekte, zufällige oder Folgeschäden — einschließlich Verlust von bei einem Drittanbieter eingesetztem Geld —, die aus Ihrer Nutzung der Website oder dem Vertrauen auf deren Inhalte entstehen.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>10. Kündigung</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Wir können Ihren Zugang zur Website jederzeit ohne vorherige Ankündigung einschränken oder sperren, wenn wir der Ansicht sind, dass Sie gegen diese Bedingungen verstoßen haben.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>11. Anwendbares Recht</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Diese Bedingungen unterliegen dem Recht Albaniens, unter Ausschluss seiner Kollisionsnormen. Streitigkeiten aus diesen Bedingungen unterliegen der ausschließlichen Zuständigkeit der zuständigen Gerichte Albaniens.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>12. Kontakt</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Fragen zu diesen Nutzungsbedingungen können Sie uns über unsere <a href="{$url['contact']['de']}">Kontaktseite</a> senden.</p><!-- /wp:paragraph -->
HTML,
	'zh' => <<<HTML
<!-- wp:heading --><h2>1. 接受条款</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>访问或使用CasinoRadar（"本网站"）即表示您同意受本使用条款约束。如果您不同意，请不要使用本网站。我们可能会不时更新本条款；条款更新后继续使用本网站即表示您接受修订后的条款。</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>2. 内容与知识产权</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>本网站上的所有文字、图形、标志和软件均归Velaro Ads所有或经其授权使用，并受适用的版权和商标法保护。未经我们事先书面许可，您不得复制、传播本网站任何部分或据此创作衍生作品。</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>3. 许可使用</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>本网站仅供您个人非商业性使用。您可以出于信息目的查看和分享单个页面，但不得移除任何版权或署名声明。</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>4. 禁止行为</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>您同意不将本网站用于任何非法目的；不试图干扰本网站的安全性或正常运行；不使用机器人、爬虫或其他自动化工具访问或提取内容；未经我们书面同意，不得复制、转售或以其他方式商业利用本网站的任何部分。</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>5. 账户安全</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>如果本网站提供账户功能（如新闻通讯订阅或评论提交），您需对与账户相关的凭证保密负责，并在发现任何未经授权的使用时立即通知我们。</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>6. 联盟营销披露</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>CasinoRadar是一家联盟营销网站。当您通过本网站的链接在在线赌场注册或存款时，我们可能会获得佣金。此佣金绝不会影响我们评测、评级或排名的独立性和客观性——每家运营商均按照相同的公开方法论进行评估，与任何商业关系无关。</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>7. 第三方链接</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>本网站包含指向第三方网站的链接，包括我们评测的在线赌场。我们不对任何第三方网站的内容、条款或隐私做法负责，收录链接并不代表我们对该运营商服务的认可。</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>8. 免责声明</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>本网站及其内容均按"现状"和"现有"提供，不附带任何明示或暗示的担保。我们不保证网站上的信息完整、准确或最新，建议您在存款前始终直接向运营商核实赌场的最新条款。</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>9. 责任限制</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>在法律允许的最大范围内，Velaro Ads不对因使用本网站或依赖其内容而产生的任何间接、附带或后果性损害（包括在第三方运营商处投注的资金损失）承担责任。</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>10. 终止</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>如果我们认为您违反了本条款，我们可能会随时暂停或限制您对本网站的访问，恕不另行通知。</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>11. 适用法律</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>本条款受阿尔巴尼亚法律管辖，不考虑其法律冲突原则。因本条款引起的任何争议均应受阿尔巴尼亚有管辖权法院的专属管辖。</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>12. 联系我们</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>如对本使用条款有任何疑问，请通过我们的<a href="{$url['contact']['zh']}">联系页面</a>与我们联系。</p><!-- /wp:paragraph -->
HTML,
);

$privacy_content = array(
	'en' => <<<HTML
<!-- wp:heading --><h2>1. Introduction</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>This Privacy Policy explains how Velaro Ads ("we", "us", or "our"), the operator of CasinoRadar, collects, uses, and protects information when you visit our Site. By using the Site, you consent to the practices described in this Policy.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>2. Information We Collect</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>We may collect personal information you provide directly, such as your name and email address, when you subscribe to our newsletter, submit a comment or review, or contact us through the Site.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>3. Automatically Collected Data</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Like most websites, we automatically collect certain information when you visit, including your IP address, browser type, device information, pages viewed, and referring URLs. This data helps us understand how the Site is used and improve its performance.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>4. How We Use Your Information</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>We use collected information to operate and improve the Site, respond to your inquiries, send newsletters you've subscribed to, moderate user submissions, and analyze site traffic and usage patterns.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>5. Cookies</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>We use cookies and similar technologies to operate the Site and understand how visitors use it. See our <a href="{$url['cookies']['en']}">Cookies Policy</a> for full details on the types of cookies we use and how to manage them.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>6. Third-Party Advertising &amp; Affiliate Links</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>The Site contains affiliate links to third-party online casinos. We may work with advertising and analytics partners who use cookies or similar technologies to measure the effectiveness of these links. We do not control these third parties' own data practices; please review their respective privacy policies.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>7. Information Sharing</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>We do not sell your personal information. We may share information with trusted service providers who help us operate the Site (such as email and hosting providers), or when required to do so by law.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>8. Data Retention &amp; Security</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>We retain personal information only as long as necessary for the purposes described in this Policy. We use reasonable technical and organizational measures, including SSL encryption, to protect your information, though no method of transmission or storage is completely secure.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>9. Your Rights &amp; Choices</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>You may request access to, correction of, or deletion of your personal information at any time by contacting us. You can unsubscribe from our newsletter at any time using the link in any email we send.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>10. Children's Privacy</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>CasinoRadar is intended for users aged 18 and over and is not directed at children. We do not knowingly collect personal information from anyone under 18; if we become aware that we have, we will delete it promptly.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>11. International Data Transfers</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Your information may be processed in countries other than your own, which may have different data protection laws. By using the Site, you consent to this transfer and processing.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>12. Changes to This Policy</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>We may update this Privacy Policy from time to time. Any changes will be posted on this page, and continued use of the Site after an update constitutes acceptance of the revised Policy.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>13. Contact Us</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>If you have questions about this Privacy Policy or how your information is handled, please reach out via our <a href="{$url['contact']['en']}">Contact page</a>.</p><!-- /wp:paragraph -->
HTML,
	'de' => <<<HTML
<!-- wp:heading --><h2>1. Einleitung</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Diese Datenschutzerklärung erläutert, wie Velaro Ads ("wir", "uns" oder "unser"), der Betreiber von CasinoRadar, Informationen erhebt, verwendet und schützt, wenn Sie unsere Website besuchen. Durch die Nutzung der Website stimmen Sie den in dieser Erklärung beschriebenen Praktiken zu.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>2. Von Uns Erhobene Informationen</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Wir erheben möglicherweise personenbezogene Daten, die Sie uns direkt zur Verfügung stellen, wie Ihren Namen und Ihre E-Mail-Adresse, wenn Sie unseren Newsletter abonnieren, einen Kommentar oder eine Bewertung abgeben oder uns über die Website kontaktieren.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>3. Automatisch Erfasste Daten</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Wie die meisten Websites erfassen wir automatisch bestimmte Informationen, wenn Sie uns besuchen, darunter Ihre IP-Adresse, Browsertyp, Geräteinformationen, aufgerufene Seiten und verweisende URLs. Diese Daten helfen uns zu verstehen, wie die Website genutzt wird, und ihre Leistung zu verbessern.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>4. Wie Wir Ihre Informationen Verwenden</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Wir verwenden erhobene Informationen, um die Website zu betreiben und zu verbessern, auf Ihre Anfragen zu antworten, abonnierte Newsletter zu versenden, Nutzerbeiträge zu moderieren und Website-Traffic sowie Nutzungsmuster zu analysieren.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>5. Cookies</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Wir verwenden Cookies und ähnliche Technologien, um die Website zu betreiben und zu verstehen, wie Besucher sie nutzen. Einzelheiten zu den von uns verwendeten Cookie-Arten und deren Verwaltung finden Sie in unserer <a href="{$url['cookies']['de']}">Cookie-Richtlinie</a>.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>6. Werbung Dritter &amp; Partnerlinks</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Die Website enthält Partnerlinks zu Online-Casinos Dritter. Wir arbeiten möglicherweise mit Werbe- und Analysepartnern zusammen, die Cookies oder ähnliche Technologien verwenden, um die Wirksamkeit dieser Links zu messen. Wir haben keine Kontrolle über die Datenschutzpraktiken dieser Dritten; bitte prüfen Sie deren jeweilige Datenschutzerklärungen.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>7. Weitergabe von Informationen</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Wir verkaufen Ihre personenbezogenen Daten nicht. Wir geben Informationen möglicherweise an vertrauenswürdige Dienstleister weiter, die uns beim Betrieb der Website unterstützen (z. B. E-Mail- und Hosting-Anbieter), oder wenn dies gesetzlich vorgeschrieben ist.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>8. Datenspeicherung &amp; Sicherheit</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Wir speichern personenbezogene Daten nur so lange, wie es für die in dieser Erklärung beschriebenen Zwecke erforderlich ist. Wir setzen angemessene technische und organisatorische Maßnahmen ein, einschließlich SSL-Verschlüsselung, um Ihre Informationen zu schützen, wobei keine Übertragungs- oder Speichermethode vollständig sicher ist.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>9. Ihre Rechte &amp; Wahlmöglichkeiten</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Sie können jederzeit Zugang zu, Berichtigung oder Löschung Ihrer personenbezogenen Daten beantragen, indem Sie uns kontaktieren. Sie können sich jederzeit über den Link in jeder E-Mail von unserem Newsletter abmelden.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>10. Datenschutz für Kinder</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>CasinoRadar richtet sich an Nutzer ab 18 Jahren und ist nicht für Kinder bestimmt. Wir erheben wissentlich keine personenbezogenen Daten von Personen unter 18 Jahren; sollten wir feststellen, dass dies geschehen ist, werden wir diese umgehend löschen.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>11. Internationale Datenübermittlung</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Ihre Informationen können in anderen Ländern als Ihrem eigenen verarbeitet werden, die möglicherweise andere Datenschutzgesetze haben. Durch die Nutzung der Website stimmen Sie dieser Übermittlung und Verarbeitung zu.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>12. Änderungen Dieser Erklärung</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Wir können diese Datenschutzerklärung von Zeit zu Zeit aktualisieren. Änderungen werden auf dieser Seite veröffentlicht, und die fortgesetzte Nutzung der Website nach einer Aktualisierung gilt als Zustimmung zur überarbeiteten Erklärung.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>13. Kontaktieren Sie Uns</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Bei Fragen zu dieser Datenschutzerklärung oder zum Umgang mit Ihren Daten wenden Sie sich bitte über unsere <a href="{$url['contact']['de']}">Kontaktseite</a> an uns.</p><!-- /wp:paragraph -->
HTML,
	'zh' => <<<HTML
<!-- wp:heading --><h2>1. 简介</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>本隐私政策说明CasinoRadar的运营方Velaro Ads（"我们"）在您访问本网站时如何收集、使用和保护信息。使用本网站即表示您同意本政策所述的做法。</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>2. 我们收集的信息</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>当您订阅我们的新闻通讯、提交评论或评测，或通过本网站联系我们时，我们可能会收集您直接提供的个人信息，如姓名和电子邮件地址。</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>3. 自动收集的数据</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>与大多数网站一样，我们会在您访问时自动收集某些信息，包括您的IP地址、浏览器类型、设备信息、浏览页面和引荐网址。这些数据帮助我们了解网站的使用情况并改善其性能。</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>4. 我们如何使用您的信息</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>我们使用收集的信息来运营和改进本网站、回复您的咨询、发送您订阅的新闻通讯、审核用户提交的内容，以及分析网站流量和使用模式。</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>5. Cookie</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>我们使用Cookie和类似技术来运营本网站并了解访客如何使用它。有关我们使用的Cookie类型及管理方式的完整详情，请参阅我们的<a href="{$url['cookies']['zh']}">Cookie政策</a>。</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>6. 第三方广告与联盟链接</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>本网站包含指向第三方在线赌场的联盟链接。我们可能与使用Cookie或类似技术来衡量这些链接效果的广告和分析合作伙伴合作。我们不控制这些第三方自身的数据处理方式；请查阅其各自的隐私政策。</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>7. 信息共享</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>我们不会出售您的个人信息。我们可能会与协助我们运营本网站的可信服务提供商（如电子邮件和托管服务提供商）共享信息，或在法律要求时进行共享。</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>8. 数据保留与安全</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>我们仅在本政策所述目的所需的期限内保留个人信息。我们采用合理的技术和组织措施（包括SSL加密）来保护您的信息，但没有任何传输或存储方式是完全安全的。</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>9. 您的权利与选择</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>您可以随时通过联系我们来请求访问、更正或删除您的个人信息。您可以随时使用我们发送的任何邮件中的链接取消订阅新闻通讯。</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>10. 儿童隐私</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>CasinoRadar面向18岁及以上的用户，不面向儿童。我们不会故意收集18岁以下人士的个人信息；如果我们发现已收集此类信息，将立即予以删除。</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>11. 国际数据传输</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>您的信息可能会在您所在国家/地区以外的国家进行处理，这些国家可能有不同的数据保护法律。使用本网站即表示您同意此类传输和处理。</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>12. 本政策的变更</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>我们可能会不时更新本隐私政策。任何变更都将发布在本页面上，政策更新后继续使用本网站即表示接受修订后的政策。</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>13. 联系我们</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>如对本隐私政策或您的信息处理方式有任何疑问，请通过我们的<a href="{$url['contact']['zh']}">联系页面</a>与我们联系。</p><!-- /wp:paragraph -->
HTML,
);

$cookies_content = array(
	'en' => <<<HTML
<!-- wp:heading --><h2>Introduction</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>CasinoRadar uses cookies and similar technologies to make the Site work properly, understand how it's used, and — where applicable — support advertising. This Cookies Policy explains what cookies are and how we use them.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>What Are Cookies?</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Cookies are small text files stored on your device when you visit a website. They allow the site to recognize your browser, remember your preferences, and collect information about how the site is used.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>Types of Cookies We Use</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p><strong>Essential Cookies</strong> — Required for core site functions such as navigation and secure access. Without these, parts of the Site may not work properly.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><strong>Performance &amp; Analytics Cookies</strong> — Help us understand how visitors interact with the Site so we can improve content and layout.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><strong>Functional Cookies</strong> — Remember your preferences, such as your selected language, to personalize your experience.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><strong>Advertising &amp; Affiliate Tracking Cookies</strong> — Used to measure the performance of affiliate links to casino operators and, where applicable, to show relevant offers.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><strong>Third-Party Cookies</strong> — Set by services we use, such as analytics providers, which may also track activity across other sites.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>Managing Your Cookie Preferences</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Most browsers let you control or delete cookies through their settings. Disabling cookies may limit some features of the Site. Visit your browser's help pages for instructions specific to your browser.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>Changes to This Policy</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>We may update this Cookies Policy periodically to reflect changes in the cookies we use or for legal reasons. Updates will be posted on this page.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>Contact Us</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Questions about our use of cookies can be sent via our <a href="{$url['contact']['en']}">Contact page</a>.</p><!-- /wp:paragraph -->
HTML,
	'de' => <<<HTML
<!-- wp:heading --><h2>Einleitung</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>CasinoRadar verwendet Cookies und ähnliche Technologien, damit die Website ordnungsgemäß funktioniert, um zu verstehen, wie sie genutzt wird, und – soweit zutreffend – um Werbung zu unterstützen. Diese Cookie-Richtlinie erklärt, was Cookies sind und wie wir sie verwenden.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>Was Sind Cookies?</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Cookies sind kleine Textdateien, die auf Ihrem Gerät gespeichert werden, wenn Sie eine Website besuchen. Sie ermöglichen es der Website, Ihren Browser wiederzuerkennen, Ihre Präferenzen zu speichern und Informationen über die Nutzung der Website zu sammeln.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>Von Uns Verwendete Cookie-Arten</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p><strong>Essenzielle Cookies</strong> — Erforderlich für Kernfunktionen der Website wie Navigation und sicheren Zugang. Ohne diese funktionieren Teile der Website möglicherweise nicht ordnungsgemäß.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><strong>Leistungs- &amp; Analyse-Cookies</strong> — Helfen uns zu verstehen, wie Besucher mit der Website interagieren, damit wir Inhalte und Layout verbessern können.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><strong>Funktionale Cookies</strong> — Speichern Ihre Präferenzen, wie Ihre gewählte Sprache, um Ihr Erlebnis zu personalisieren.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><strong>Werbe- &amp; Partnerprogramm-Tracking-Cookies</strong> — Werden verwendet, um die Leistung von Partnerlinks zu Casino-Anbietern zu messen und, soweit zutreffend, relevante Angebote anzuzeigen.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><strong>Cookies Dritter</strong> — Werden von Diensten gesetzt, die wir nutzen, wie Analyseanbietern, die Aktivitäten möglicherweise auch auf anderen Websites verfolgen.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>Verwaltung Ihrer Cookie-Einstellungen</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Die meisten Browser erlauben es Ihnen, Cookies über ihre Einstellungen zu kontrollieren oder zu löschen. Das Deaktivieren von Cookies kann einige Funktionen der Website einschränken. Besuchen Sie die Hilfeseiten Ihres Browsers für spezifische Anweisungen.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>Änderungen Dieser Richtlinie</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Wir können diese Cookie-Richtlinie regelmäßig aktualisieren, um Änderungen bei den verwendeten Cookies oder aus rechtlichen Gründen widerzuspiegeln. Aktualisierungen werden auf dieser Seite veröffentlicht.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>Kontaktieren Sie Uns</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Fragen zu unserer Verwendung von Cookies können Sie über unsere <a href="{$url['contact']['de']}">Kontaktseite</a> senden.</p><!-- /wp:paragraph -->
HTML,
	'zh' => <<<HTML
<!-- wp:heading --><h2>简介</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>CasinoRadar使用Cookie和类似技术，使网站正常运行、了解其使用情况，并在适用的情况下支持广告投放。本Cookie政策说明什么是Cookie以及我们如何使用它们。</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>什么是Cookie？</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Cookie是您访问网站时存储在您设备上的小型文本文件。它们使网站能够识别您的浏览器、记住您的偏好设置，并收集有关网站使用情况的信息。</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>我们使用的Cookie类型</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p><strong>必要Cookie</strong>——用于导航和安全访问等核心网站功能所必需。如果没有这些Cookie，网站的部分功能可能无法正常运行。</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><strong>性能与分析Cookie</strong>——帮助我们了解访客如何与网站互动，以便我们改进内容和布局。</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><strong>功能性Cookie</strong>——记住您的偏好设置，例如您选择的语言，以个性化您的浏览体验。</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><strong>广告与联盟追踪Cookie</strong>——用于衡量指向赌场运营商的联盟链接的效果，并在适用时展示相关优惠。</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><strong>第三方Cookie</strong>——由我们使用的服务（如分析服务提供商）设置，这些服务也可能追踪您在其他网站上的活动。</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>管理您的Cookie偏好</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>大多数浏览器允许您通过设置控制或删除Cookie。禁用Cookie可能会限制本网站的部分功能。请访问您浏览器的帮助页面以获取具体说明。</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>本政策的变更</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>我们可能会定期更新本Cookie政策，以反映我们所使用Cookie的变化或出于法律原因。更新内容将发布在本页面上。</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>联系我们</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>如对我们使用Cookie的方式有任何疑问，请通过我们的<a href="{$url['contact']['zh']}">联系页面</a>与我们联系。</p><!-- /wp:paragraph -->
HTML,
);

$imprint_content = array(
	'en' => <<<HTML
<!-- wp:paragraph --><p><strong>Business Name:</strong> Velaro Ads</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><strong>Address:</strong> Tirane, Myslym Keta, Albania 1001</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><strong>Email:</strong> contact@casinoradar.io</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><strong>Represented By:</strong> Mateo Demirlika</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><strong>Registered In:</strong> Albania</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><strong>Commercial Registration Number:</strong> M52021016U</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>CasinoRadar is operated by Velaro Ads. Every effort is made to keep the information on this Site accurate and up to date; however, Velaro Ads accepts no liability for the accuracy, completeness, or timeliness of the information provided.</p><!-- /wp:paragraph -->
HTML,
	'de' => <<<HTML
<!-- wp:paragraph --><p><strong>Firmenname:</strong> Velaro Ads</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><strong>Adresse:</strong> Tirane, Myslym Keta, Albanien 1001</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><strong>E-Mail:</strong> contact@casinoradar.io</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><strong>Vertreten Durch:</strong> Mateo Demirlika</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><strong>Registriert In:</strong> Albanien</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><strong>Handelsregisternummer:</strong> M52021016U</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>CasinoRadar wird von Velaro Ads betrieben. Wir bemühen uns, die Informationen auf dieser Website korrekt und aktuell zu halten; Velaro Ads übernimmt jedoch keine Haftung für die Richtigkeit, Vollständigkeit oder Aktualität der bereitgestellten Informationen.</p><!-- /wp:paragraph -->
HTML,
	'zh' => <<<HTML
<!-- wp:paragraph --><p><strong>公司名称：</strong>Velaro Ads</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><strong>地址：</strong>阿尔巴尼亚地拉那，Myslym Keta，1001</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><strong>电子邮件：</strong>contact@casinoradar.io</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><strong>法定代表人：</strong>Mateo Demirlika</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><strong>注册国家：</strong>阿尔巴尼亚</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><strong>商业注册号：</strong>M52021016U</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>CasinoRadar由Velaro Ads运营。我们努力确保本网站上的信息准确且为最新，但Velaro Ads对所提供信息的准确性、完整性或时效性不承担任何责任。</p><!-- /wp:paragraph -->
HTML,
);

foreach ( array( 'en', 'de', 'zh' ) as $lang ) {
	if ( ! empty( $ids['terms'][ $lang ] ) ) {
		wp_update_post( array( 'ID' => $ids['terms'][ $lang ], 'post_content' => $terms_content[ $lang ] ) );
	}
	if ( ! empty( $ids['cookies'][ $lang ] ) ) {
		wp_update_post( array( 'ID' => $ids['cookies'][ $lang ], 'post_content' => $cookies_content[ $lang ] ) );
	}
	if ( ! empty( $ids['imprint'][ $lang ] ) ) {
		wp_update_post( array( 'ID' => $ids['imprint'][ $lang ], 'post_content' => $imprint_content[ $lang ] ) );
	}
	if ( ! empty( $ids['privacy'][ $lang ] ) ) {
		wp_update_post( array( 'ID' => $ids['privacy'][ $lang ], 'post_content' => $privacy_content[ $lang ] ) );
	}
}

WP_CLI::success( 'Legal pages added/updated: Terms of Use, Cookies Policy, Imprint (new), Privacy Policy (replaced placeholder).' );
