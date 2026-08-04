<?php
/**
 * Page-body markup for the site's composed pages (Home, Guide, News,
 * Reviews, Complaints) — defined once here and used by wp-cli/seed.php so
 * the demo pages start out already composed. Also registered as block
 * patterns so an editor can re-insert a page's default layout later.
 *
 * Each function takes a $lang ('en'/'de'/'zh'/'es') because these are baked once
 * into a language-specific Page's post_content at seed time — unlike the
 * header/footer nav chrome (translated at render time via Polylang string
 * translations), this static heading/paragraph text needs to be correct
 * per-language *when the page is created*.
 */

defined( 'ABSPATH' ) || exit;

/** Look up one UI string for $lang, falling back to the English original. */
function wagerwise_pattern_string( array $strings, string $lang ): string {
	return $strings[ $lang ] ?? $strings['en'];
}

function wagerwise_homepage_pattern_markup( string $lang = 'en' ): string {
	$s = fn( array $strings ) => esc_html( wagerwise_pattern_string( $strings, $lang ) );

	$top_picks   = $s( array( 'en' => 'Top Picks This Week', 'de' => 'Top-Empfehlungen der Woche', 'zh' => '本周精选推荐', 'es' => 'Mejores Opciones de la Semana', 'pt' => 'Melhores Escolhas da Semana', 'fr' => 'Meilleurs Choix de la Semaine', 'it' => 'Le Migliori Scelte della Settimana', 'sv' => 'Veckans Bästa Val', 'nb' => 'Ukens Beste Valg', 'fi' => 'Viikon Parhaat Valinnat', 'nl' => 'Beste Keuzes van de Week', 'ja' => '今週のトップピック', 'pl' => 'Najlepsze Typy Tygodnia', 'cs' => 'Nejlepší Tipy Týdne', 'ro' => 'Cele Mai Bune Alegeri ale Săptămânii', 'el' => 'Καλύτερες Επιλογές της Εβδομάδας' ) );
	$live_tourn  = $s( array( 'en' => 'Live Tournaments', 'de' => 'Laufende Turniere', 'zh' => '正在进行的锦标赛', 'es' => 'Torneos en Vivo', 'pt' => 'Torneios ao Vivo', 'fr' => 'Tournois en Direct', 'it' => 'Tornei dal Vivo', 'sv' => 'Liveturneringar', 'nb' => 'Live-turneringer', 'fi' => 'Live-turnaukset', 'nl' => 'Live Toernooien', 'ja' => 'ライブトーナメント', 'pl' => 'Turnieje na Żywo', 'cs' => 'Živé Turnaje', 'ro' => 'Turnee Live', 'el' => 'Ζωντανά Τουρνουά' ) );
	$why_trust   = $s( array( 'en' => 'Why Trust CasinoRadar', 'de' => 'Warum CasinoRadar Vertrauen', 'zh' => '为何信赖CasinoRadar', 'es' => 'Por Qué Confiar en CasinoRadar', 'pt' => 'Por Que Confiar na CasinoRadar', 'fr' => 'Pourquoi Faire Confiance à CasinoRadar', 'it' => 'Perché Fidarsi di CasinoRadar', 'sv' => 'Varför Lita på CasinoRadar', 'nb' => 'Hvorfor Stole på CasinoRadar', 'fi' => 'Miksi Luottaa CasinoRadariin', 'nl' => 'Waarom CasinoRadar Vertrouwen', 'ja' => 'CasinoRadarが信頼される理由', 'pl' => 'Dlaczego Warto Zaufać CasinoRadar', 'cs' => 'Proč Důvěřovat CasinoRadar', 'ro' => 'De Ce Să Ai Încredere în CasinoRadar', 'el' => 'Γιατί να Εμπιστευτείτε το CasinoRadar' ) );
	$trust_1     = $s( array( 'en' => '✓ Every casino is manually reviewed against our published methodology', 'de' => '✓ Jedes Casino wird manuell nach unserer veröffentlichten Methodik geprüft', 'zh' => '✓ 每家赌场均按我们公开的方法论人工审核', 'es' => '✓ Cada casino es revisado manualmente según nuestra metodología publicada', 'pt' => '✓ Cada casino é analisado manualmente de acordo com a nossa metodologia publicada', 'fr' => '✓ Chaque casino est examiné manuellement selon notre méthodologie publiée', 'it' => '✓ Ogni casinò viene esaminato manualmente secondo la nostra metodologia pubblicata', 'sv' => '✓ Varje casino granskas manuellt enligt vår publicerade metodik', 'nb' => '✓ Hvert casino blir manuelt vurdert etter vår publiserte metodikk', 'fi' => '✓ Jokainen kasino tarkistetaan manuaalisesti julkaistun menetelmämme mukaisesti', 'nl' => '✓ Elk casino wordt handmatig beoordeeld volgens onze gepubliceerde methodologie', 'ja' => '✓ すべてのカジノは公開された方法論に基づき手動で審査されています', 'pl' => '✓ Każde kasyno jest ręcznie sprawdzane zgodnie z naszą opublikowaną metodologią', 'cs' => '✓ Každé kasino je ručně prověřováno podle naší zveřejněné metodiky', 'ro' => '✓ Fiecare cazinou este verificat manual conform metodologiei noastre publicate', 'el' => '✓ Κάθε καζίνο ελέγχεται χειροκίνητα σύμφωνα με τη δημοσιευμένη μεθοδολογία μας' ) );
	$trust_2     = $s( array( 'en' => '✓ Ratings weigh licensing, payout speed, and bonus fairness', 'de' => '✓ Bewertungen berücksichtigen Lizenzierung, Auszahlungsgeschwindigkeit und faire Boni', 'zh' => '✓ 评级综合考量牌照、提款速度与红利公平性', 'es' => '✓ Las calificaciones consideran la licencia, la velocidad de pago y la equidad de los bonos', 'pt' => '✓ As classificações consideram licenciamento, velocidade de pagamento e justiça dos bónus', 'fr' => '✓ Les notes tiennent compte de la licence, de la rapidité des paiements et de l\'équité des bonus', 'it' => '✓ Le valutazioni considerano licenza, velocità di pagamento ed equità dei bonus', 'sv' => '✓ Betygen väger in licensiering, utbetalningshastighet och bonusrättvisa', 'nb' => '✓ Vurderingene vektlegger lisensiering, utbetalingshastighet og rettferdige bonusvilkår', 'fi' => '✓ Arvioinneissa painotetaan lisensointia, maksunopeutta ja bonusehtojen oikeudenmukaisuutta', 'nl' => '✓ Beoordelingen wegen licenties, uitbetalingssnelheid en eerlijkheid van bonussen mee', 'ja' => '✓ 評価はライセンス、出金速度、ボーナスの公正さを考慮しています', 'pl' => '✓ Oceny uwzględniają licencję, szybkość wypłat i uczciwość warunków bonusowych', 'cs' => '✓ Hodnocení zohledňují licenci, rychlost výplat a spravedlivost bonusových podmínek', 'ro' => '✓ Evaluările iau în calcul licența, viteza de plată și corectitudinea condițiilor bonusurilor', 'el' => '✓ Οι βαθμολογίες συνεκτιμούν την αδειοδότηση, την ταχύτητα πληρωμών και τη δικαιοσύνη των όρων μπόνους' ) );
	$trust_3     = $s( array( 'en' => '✓ Licences are cross-checked against MGA, UKGC, and Curaçao registries', 'de' => '✓ Lizenzen werden mit MGA-, UKGC- und Curaçao-Registern abgeglichen', 'zh' => '✓ 牌照信息与MGA、UKGC及库拉索登记系统交叉核实', 'es' => '✓ Las licencias se verifican con los registros de MGA, UKGC y Curazao', 'pt' => '✓ As licenças são verificadas com os registos da MGA, UKGC e Curaçao', 'fr' => '✓ Les licences sont vérifiées auprès des registres MGA, UKGC et Curaçao', 'it' => '✓ Le licenze vengono verificate con i registri MGA, UKGC e Curaçao', 'sv' => '✓ Licenser kontrolleras mot MGA-, UKGC- och Curaçao-register', 'nb' => '✓ Lisenser blir kryssjekket mot MGA-, UKGC- og Curaçao-registre', 'fi' => '✓ Lisenssit tarkistetaan MGA-, UKGC- ja Curaçao-rekistereistä', 'nl' => '✓ Licenties worden gecontroleerd bij de MGA-, UKGC- en Curaçao-registers', 'ja' => '✓ ライセンスはMGA、UKGC、キュラソーの登録情報と照合されています', 'pl' => '✓ Licencje są weryfikowane w rejestrach MGA, UKGC i Curaçao', 'cs' => '✓ Licence jsou křížově kontrolovány v registrech MGA, UKGC a Curaçao', 'ro' => '✓ Licențele sunt verificate în registrele MGA, UKGC și Curaçao', 'el' => '✓ Οι άδειες ελέγχονται διασταυρωτικά με τα μητρώα MGA, UKGC και Κουρασάο' ) );
	$trust_4     = $s( array( 'en' => '✓ Reviews are revisited as operators change their terms', 'de' => '✓ Testberichte werden aktualisiert, sobald Anbieter ihre Bedingungen ändern', 'zh' => '✓ 运营商条款变更时，评测将同步更新', 'es' => '✓ Las reseñas se actualizan cuando los operadores cambian sus condiciones', 'pt' => '✓ As análises são revistas sempre que os operadores alteram as suas condições', 'fr' => '✓ Les avis sont révisés lorsque les opérateurs modifient leurs conditions', 'it' => '✓ Le recensioni vengono aggiornate quando gli operatori modificano le loro condizioni', 'sv' => '✓ Recensioner uppdateras när operatörer ändrar sina villkor', 'nb' => '✓ Anmeldelser oppdateres når operatører endrer sine vilkår', 'fi' => '✓ Arvostelut päivitetään, kun operaattorit muuttavat ehtojaan', 'nl' => '✓ Reviews worden herzien wanneer aanbieders hun voorwaarden wijzigen', 'ja' => '✓ 運営会社が規約を変更した場合、レビューは再確認されます', 'pl' => '✓ Recenzje są aktualizowane, gdy operatorzy zmieniają swoje warunki', 'cs' => '✓ Recenze jsou aktualizovány, když provozovatelé změní své podmínky', 'ro' => '✓ Recenziile sunt actualizate atunci când operatorii își schimbă condițiile', 'el' => '✓ Οι κριτικές επικαιροποιούνται όταν οι φορείς εκμετάλλευσης αλλάζουν τους όρους τους' ) );
	$latest_news = $s( array( 'en' => 'Latest News', 'de' => 'Aktuelle Neuigkeiten', 'zh' => '最新资讯', 'es' => 'Últimas Noticias', 'pt' => 'Últimas Notícias', 'fr' => 'Actualités Récentes', 'it' => 'Ultime Notizie', 'sv' => 'Senaste Nyheterna', 'nb' => 'Siste Nytt', 'fi' => 'Viimeisimmät Uutiset', 'nl' => 'Laatste Nieuws', 'ja' => '最新ニュース', 'pl' => 'Najnowsze Wiadomości', 'cs' => 'Nejnovější Zprávy', 'ro' => 'Ultimele Știri', 'el' => 'Τελευταία Νέα' ) );

	return <<<HTML
<!-- wp:wagerwise/hero-search /-->

<!-- wp:group {"className":"ww-section ww-section--tight","layout":{"type":"constrained"}} -->
<div class="wp-block-group ww-section ww-section--tight">
	<!-- wp:wagerwise/category-strip {"style":"chip"} /-->
</div>
<!-- /wp:group -->

<!-- wp:group {"className":"ww-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group ww-section">
	<!-- wp:heading {"level":2} -->
	<h2>{$top_picks}</h2>
	<!-- /wp:heading -->
	<!-- wp:wagerwise/top-casinos {"number":3,"featuredOnly":true,"layout":"grid"} /-->
</div>
<!-- /wp:group -->

<!-- wp:group {"className":"ww-section ww-section--alt","layout":{"type":"constrained"}} -->
<div class="wp-block-group ww-section ww-section--alt">
	<!-- wp:heading {"level":2} -->
	<h2>{$live_tourn}</h2>
	<!-- /wp:heading -->
	<!-- wp:wagerwise/tournament-grid {"number":3} /-->
</div>
<!-- /wp:group -->

<!-- wp:group {"className":"ww-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group ww-section">
	<!-- wp:columns -->
	<div class="wp-block-columns">
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:heading {"level":2} -->
			<h2>{$why_trust}</h2>
			<!-- /wp:heading -->
			<!-- wp:paragraph -->
			<p>{$trust_1}</p>
			<!-- /wp:paragraph -->
			<!-- wp:paragraph -->
			<p>{$trust_2}</p>
			<!-- /wp:paragraph -->
			<!-- wp:paragraph -->
			<p>{$trust_3}</p>
			<!-- /wp:paragraph -->
			<!-- wp:paragraph -->
			<p>{$trust_4}</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:heading {"level":2} -->
			<h2>{$latest_news}</h2>
			<!-- /wp:heading -->
			<!-- wp:wagerwise/news-grid {"number":2} /-->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
HTML;
}

function wagerwise_guide_page_markup( string $lang = 'en' ): string {
	$s = fn( array $strings ) => esc_html( wagerwise_pattern_string( $strings, $lang ) );

	$h1      = $s( array( 'en' => 'Player Guide', 'de' => 'Spieler-Ratgeber', 'zh' => '玩家指南', 'es' => 'Guía del Jugador', 'pt' => 'Guia do Jogador', 'fr' => 'Guide du Joueur', 'it' => 'Guida del Giocatore', 'sv' => 'Spelarguide', 'nb' => 'Spillerguide', 'fi' => 'Pelaajan Opas', 'nl' => 'Spelersgids', 'ja' => 'プレイヤーガイド', 'pl' => 'Przewodnik Gracza', 'cs' => 'Průvodce pro Hráče', 'ro' => 'Ghidul Jucătorului', 'el' => 'Οδηγός Παίκτη' ) );
	$subtitle = $s( array( 'en' => 'Straightforward explainers, no jargon, no upsell.', 'de' => 'Klare Erklärungen, ohne Fachjargon, ohne Verkaufsdruck.', 'zh' => '简单明了的说明，没有行话，没有推销。', 'es' => 'Explicaciones claras, sin jerga, sin ventas encubiertas.', 'pt' => 'Explicações claras, sem jargão, sem venda forçada.', 'fr' => 'Des explications claires, sans jargon, sans argumentaire de vente.', 'it' => 'Spiegazioni chiare, senza gergo, senza tentativi di vendita.', 'sv' => 'Enkla förklaringar, utan jargong, utan säljknep.', 'nb' => 'Enkle forklaringer, uten sjargong, uten salgspress.', 'fi' => 'Selkeitä selityksiä, ilman ammattislangia, ilman myyntipuhetta.', 'nl' => 'Duidelijke uitleg, zonder jargon, zonder verkooppraatjes.', 'ja' => '専門用語なし、押し売りなしの分かりやすい解説。', 'pl' => 'Jasne wyjaśnienia, bez żargonu, bez naciągania na sprzedaż.', 'cs' => 'Jasná vysvětlení, bez žargonu, bez prodejních triků.', 'ro' => 'Explicații clare, fără jargon, fără presiune de vânzare.', 'el' => 'Σαφείς εξηγήσεις, χωρίς ορολογία, χωρίς πίεση πώλησης.' ) );

	return <<<HTML
<!-- wp:group {"className":"ww-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group ww-section">
	<!-- wp:heading {"level":1} -->
	<h1>{$h1}</h1>
	<!-- /wp:heading -->
	<!-- wp:paragraph {"className":"ww-page-subtitle"} -->
	<p class="ww-page-subtitle">{$subtitle}</p>
	<!-- /wp:paragraph -->
	<!-- wp:wagerwise/guide-featured /-->
	<!-- wp:wagerwise/guide-list {"number":8} /-->
</div>
<!-- /wp:group -->
HTML;
}

function wagerwise_news_page_markup( string $lang = 'en' ): string {
	return <<<'HTML'
<!-- wp:group {"className":"ww-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group ww-section">
	<!-- wp:wagerwise/news-featured /-->
	<!-- wp:wagerwise/news-grid {"number":6,"skipFirst":true} /-->
</div>
<!-- /wp:group -->
HTML;
}

function wagerwise_reviews_page_markup( string $lang = 'en' ): string {
	$s = fn( array $strings ) => esc_html( wagerwise_pattern_string( $strings, $lang ) );

	$h1        = $s( array( 'en' => 'Player Reviews', 'de' => 'Spielerbewertungen', 'zh' => '玩家评测', 'es' => 'Reseñas de Jugadores', 'pt' => 'Avaliações de Jogadores', 'fr' => 'Avis des Joueurs', 'it' => 'Recensioni dei Giocatori', 'sv' => 'Spelarrecensioner', 'nb' => 'Spilleranmeldelser', 'fi' => 'Pelaaja-arvostelut', 'nl' => 'Spelersreviews', 'ja' => 'プレイヤーレビュー', 'pl' => 'Recenzje Graczy', 'cs' => 'Recenze Hráčů', 'ro' => 'Recenzii ale Jucătorilor', 'el' => 'Κριτικές Παικτών' ) );
	$subtitle  = $s( array( 'en' => 'Independent editorial reviews, plus real player reviews published after moderation.', 'de' => 'Unabhängige Redaktions-Testberichte sowie echte Spielerbewertungen, veröffentlicht nach Moderation.', 'zh' => '独立编辑评测，以及经审核后发布的真实玩家评价。', 'es' => 'Reseñas editoriales independientes, además de reseñas reales de jugadores publicadas tras moderación.', 'pt' => 'Análises editoriais independentes, além de avaliações reais de jogadores publicadas após moderação.', 'fr' => 'Avis éditoriaux indépendants, ainsi que de véritables avis de joueurs publiés après modération.', 'it' => 'Recensioni editoriali indipendenti, oltre a vere recensioni dei giocatori pubblicate dopo moderazione.', 'sv' => 'Oberoende redaktionella recensioner samt riktiga spelarrecensioner publicerade efter granskning.', 'nb' => 'Uavhengige redaksjonelle anmeldelser, i tillegg til ekte spilleranmeldelser publisert etter moderering.', 'fi' => 'Riippumattomia toimituksellisia arvosteluja sekä aitoja pelaaja-arvosteluja, jotka julkaistaan moderoinnin jälkeen.', 'nl' => 'Onafhankelijke redactionele reviews, plus echte spelersreviews die na moderatie worden gepubliceerd.', 'ja' => '独立した編集部によるレビューに加え、審査後に公開される実際のプレイヤーレビュー。', 'pl' => 'Niezależne recenzje redakcyjne oraz prawdziwe recenzje graczy publikowane po moderacji.', 'cs' => 'Nezávislé redakční recenze i skutečné recenze hráčů zveřejněné po moderaci.', 'ro' => 'Recenzii editoriale independente, plus recenzii reale ale jucătorilor publicate după moderare.', 'el' => 'Ανεξάρτητες συντακτικές κριτικές, καθώς και πραγματικές κριτικές παικτών που δημοσιεύονται μετά από έλεγχο.' ) );
	$editors   = $s( array( 'en' => "Editor's Reviews", 'de' => 'Redaktionelle Testberichte', 'zh' => '编辑评测', 'es' => 'Reseñas Editoriales', 'pt' => 'Análises Editoriais', 'fr' => 'Avis de la Rédaction', 'it' => 'Recensioni della Redazione', 'sv' => 'Redaktionens Recensioner', 'nb' => 'Redaksjonens Anmeldelser', 'fi' => 'Toimituksen Arvostelut', 'nl' => 'Redactionele Reviews', 'ja' => '編集部レビュー', 'pl' => 'Recenzje Redakcji', 'cs' => 'Redakční Recenze', 'ro' => 'Recenziile Redacției', 'el' => 'Κριτικές Συντακτικής Ομάδας' ) );
	$players   = $s( array( 'en' => 'Player Reviews', 'de' => 'Spielerbewertungen', 'zh' => '玩家评价', 'es' => 'Reseñas de Jugadores', 'pt' => 'Avaliações de Jogadores', 'fr' => 'Avis des Joueurs', 'it' => 'Recensioni dei Giocatori', 'sv' => 'Spelarrecensioner', 'nb' => 'Spilleranmeldelser', 'fi' => 'Pelaaja-arvostelut', 'nl' => 'Spelersreviews', 'ja' => 'プレイヤーレビュー', 'pl' => 'Recenzje Graczy', 'cs' => 'Recenze Hráčů', 'ro' => 'Recenzii ale Jucătorilor', 'el' => 'Κριτικές Παικτών' ) );

	return <<<HTML
<!-- wp:group {"className":"ww-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group ww-section">
	<!-- wp:heading {"level":1} -->
	<h1>{$h1}</h1>
	<!-- /wp:heading -->
	<!-- wp:paragraph {"className":"ww-page-subtitle"} -->
	<p class="ww-page-subtitle">{$subtitle}</p>
	<!-- /wp:paragraph -->
	<!-- wp:heading {"level":2} -->
	<h2>{$editors}</h2>
	<!-- /wp:heading -->
	<!-- wp:wagerwise/review-grid {"number":6} /-->
	<!-- wp:heading {"level":2} -->
	<h2>{$players}</h2>
	<!-- /wp:heading -->
	<!-- wp:wagerwise/reviews-list {"number":12} /-->
</div>
<!-- /wp:group -->
HTML;
}

function wagerwise_complaints_page_markup( string $lang = 'en' ): string {
	$s = fn( array $strings ) => esc_html( wagerwise_pattern_string( $strings, $lang ) );

	$h1        = $s( array( 'en' => 'Complaint Resolution Center', 'de' => 'Beschwerde-Schlichtungsstelle', 'zh' => '投诉解决中心', 'es' => 'Centro de Resolución de Quejas', 'pt' => 'Centro de Resolução de Reclamações', 'fr' => 'Centre de Résolution des Réclamations', 'it' => 'Centro di Risoluzione dei Reclami', 'sv' => 'Center för Klagomålshantering', 'nb' => 'Senter for Klagebehandling', 'fi' => 'Valituskeskus', 'nl' => 'Klachtenoplossingscentrum', 'ja' => '苦情解決センター', 'pl' => 'Centrum Rozpatrywania Skarg', 'cs' => 'Centrum pro Řešení Stížností', 'ro' => 'Centrul de Soluționare a Reclamațiilor', 'el' => 'Κέντρο Επίλυσης Παραπόνων' ) );
	$subtitle  = $s( array( 'en' => 'Free mediation between players and licensed casinos.', 'de' => 'Kostenlose Vermittlung zwischen Spielern und lizenzierten Casinos.', 'zh' => '为玩家与持牌赌场提供免费调解服务。', 'es' => 'Mediación gratuita entre jugadores y casinos con licencia.', 'pt' => 'Mediação gratuita entre jogadores e casinos licenciados.', 'fr' => 'Médiation gratuite entre les joueurs et les casinos agréés.', 'it' => 'Mediazione gratuita tra giocatori e casinò autorizzati.', 'sv' => 'Kostnadsfri medling mellan spelare och licensierade casinon.', 'nb' => 'Gratis mekling mellom spillere og lisensierte casinoer.', 'fi' => 'Ilmainen sovittelu pelaajien ja lisensoitujen kasinoiden välillä.', 'nl' => 'Gratis bemiddeling tussen spelers en gelicentieerde casino\'s.', 'ja' => 'プレイヤーとライセンスを持つカジノ間の無料仲裁。', 'pl' => 'Bezpłatna mediacja między graczami a licencjonowanymi kasynami.', 'cs' => 'Bezplatná mediace mezi hráči a licencovanými kasiny.', 'ro' => 'Mediere gratuită între jucători și cazinouri licențiate.', 'el' => 'Δωρεάν διαμεσολάβηση μεταξύ παικτών και αδειοδοτημένων καζίνο.' ) );
	$file_h    = $s( array( 'en' => 'File a Complaint', 'de' => 'Beschwerde Einreichen', 'zh' => '提交投诉', 'es' => 'Presentar una Queja', 'pt' => 'Apresentar uma Reclamação', 'fr' => 'Déposer une Réclamation', 'it' => 'Presenta un Reclamo', 'sv' => 'Lämna ett Klagomål', 'nb' => 'Send Inn en Klage', 'fi' => 'Tee Valitus', 'nl' => 'Een Klacht Indienen', 'ja' => '苦情を提出する', 'pl' => 'Złóż Skargę', 'cs' => 'Podat Stížnost', 'ro' => 'Depune o Reclamație', 'el' => 'Υποβολή Παραπόνου' ) );
	$resolved_h = $s( array( 'en' => 'Recently Resolved', 'de' => 'Kürzlich Gelöst', 'zh' => '近期已解决', 'es' => 'Resueltas Recientemente', 'pt' => 'Resolvidas Recentemente', 'fr' => 'Résolues Récemment', 'it' => 'Risolti di Recente', 'sv' => 'Nyligen Lösta', 'nb' => 'Nylig Løst', 'fi' => 'Äskettäin Ratkaistut', 'nl' => 'Onlangs Opgelost', 'ja' => '最近解決済み', 'pl' => 'Ostatnio Rozwiązane', 'cs' => 'Nedávno Vyřešeno', 'ro' => 'Rezolvate Recent', 'el' => 'Πρόσφατα Επιλυμένα' ) );

	return <<<HTML
<!-- wp:group {"className":"ww-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group ww-section">
	<!-- wp:heading {"level":1} -->
	<h1>{$h1}</h1>
	<!-- /wp:heading -->
	<!-- wp:paragraph {"className":"ww-page-subtitle"} -->
	<p class="ww-page-subtitle">{$subtitle}</p>
	<!-- /wp:paragraph -->
	<!-- wp:wagerwise/complaints-stats /-->
	<!-- wp:columns -->
	<div class="wp-block-columns">
		<!-- wp:column {"className":"ww-complaint-form-col"} -->
		<div class="wp-block-column ww-complaint-form-col" id="complaint-form">
			<!-- wp:heading {"level":2} -->
			<h2>{$file_h}</h2>
			<!-- /wp:heading -->
			<!-- wp:wagerwise/complaint-form /-->
		</div>
		<!-- /wp:column -->
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:heading {"level":2} -->
			<h2>{$resolved_h}</h2>
			<!-- /wp:heading -->
			<!-- wp:wagerwise/complaints-list {"number":6} /-->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
HTML;
}

add_action( 'init', 'wagerwise_register_patterns' );

function wagerwise_register_patterns(): void {
	$lang = function_exists( 'pll_current_language' ) ? ( pll_current_language() ?: 'en' ) : 'en';
	$patterns = array(
		'homepage-sections' => array( __( 'WagerWise: Homepage Sections', 'wagerwise' ), 'wagerwise_homepage_pattern_markup' ),
		'guide-page'        => array( __( 'WagerWise: Guide Page', 'wagerwise' ), 'wagerwise_guide_page_markup' ),
		'news-page'         => array( __( 'WagerWise: News Page', 'wagerwise' ), 'wagerwise_news_page_markup' ),
		'reviews-page'      => array( __( 'WagerWise: Reviews Page', 'wagerwise' ), 'wagerwise_reviews_page_markup' ),
		'complaints-page'   => array( __( 'WagerWise: Complaints Page', 'wagerwise' ), 'wagerwise_complaints_page_markup' ),
	);
	foreach ( $patterns as $slug => [ $title, $fn ] ) {
		register_block_pattern(
			'wagerwise/' . $slug,
			array(
				'title'      => $title,
				'categories' => array( 'wagerwise' ),
				'content'    => $fn( $lang ),
			)
		);
	}
}

add_action( 'init', 'wagerwise_register_pattern_category' );

function wagerwise_register_pattern_category(): void {
	register_block_pattern_category( 'wagerwise', array( 'label' => __( 'WagerWise', 'wagerwise' ) ) );
}
