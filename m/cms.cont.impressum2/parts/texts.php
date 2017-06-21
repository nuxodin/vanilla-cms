<?php
namespace qg;

$heading = $Cont->SET->make('Heading', 'h2')->setHandler('select')->setOptions('1', '2', '3', '4')->v;
$printPart = function($part, $text) use ($Cont, $heading) {
	$Text = $Cont->Text($part.'_h');
	!trim($Text) && $Cont->Text($part.'_h', 'de', $part);
	echo '<h'.$heading.' '.($Cont->edit? 'contenteditable cmstxt='.$Text->id : '').'>'.$Text.'</h'.$heading.'>';
	$Text = $Cont->Text($part.'_p');
	!trim($Text) && $Cont->Text($part.'_p', 'de', $text);
	echo '<p '.($Cont->edit? 'contenteditable cmstxt='.$Text->id : '').'>'.$Text.'</p><br>';
};

if ($Cont->SET['Einblenden']['Haftungsausschluss']->v) {
	$part = 'Haftungsausschluss';
	$text  = 'Der Autor übernimmt keinerlei Gewähr hinsichtlich der inhaltlichen Richtigkeit, Genauigkeit, Aktualität, Zuverlässigkeit und Vollständigkeit der Informationen.
	Haftungsansprüche gegen den Autor wegen Schäden materieller oder immaterieller Art, welche aus dem Zugriff oder der Nutzung bzw. Nichtnutzung der veröffentlichten Informationen, durch Missbrauch der Verbindung oder durch technische Störungen entstanden sind, werden ausgeschlossen.
	Alle Angebote sind unverbindlich. Der Autor behält es sich ausdrücklich vor, Teile der Seiten oder das gesamte Angebot ohne gesonderte Ankündigung zu verändern, zu ergänzen, zu löschen oder die Veröffentlichung zeitweise oder endgültig einzustellen.';
	$printPart($part, $text);
}
if ($Cont->SET['Einblenden']['Haftung für Links']->v) {
	$part = 'Haftung für Links';
	$text = 'Verweise und Links auf Webseiten Dritter liegen ausserhalb unseres Verantwortungsbereichs Es wird jegliche Verantwortung für solche Webseiten abgelehnt. Der Zugriff und die Nutzung solcher Webseiten erfolgen auf eigene Gefahr des Nutzers oder der Nutzerin.';
	$printPart($part, $text);
}
if ($Cont->SET['Einblenden']['Urheberrecht']->v) {
	$part = 'Urheberrecht';
	$text = 'Die Urheber- und alle anderen Rechte an Inhalten, Bildern, Fotos oder anderen Dateien auf der Website gehören ausschliesslich dem Betreiber der Website oder den speziell genannten  Rechtsinhabern. Für die Reproduktion jeglicher Elemente ist die schriftliche Zustimmung der Urheberrechtsträger im Voraus einzuholen.';
	$printPart($part, $text);
}
if ($Cont->SET['Einblenden']['Datenschutzerklärung']->v) {
	$part = 'Datenschutzerklärung';
	$text = 'Gestützt auf Artikel 13 der schweizerischen Bundesverfassung und die datenschutzrechtlichen Bestimmungen des Bundes (Datenschutzgesetz, DSG) hat jede Person Anspruch auf Schutz ihrer Privatsphäre sowie auf Schutz vor Missbrauch ihrer persönlichen Daten. Wir halten diese Bestimmungen ein. Persönliche Daten werden streng vertraulich behandelt und weder an Dritte verkauft noch weiter gegeben.
	In enger Zusammenarbeit mit unseren Hosting-Providern bemühen wir uns, die Datenbanken so gut wie möglich vor fremden Zugriffen, Verlusten, Missbrauch oder vor Fälschung zu schützen.
	Beim Zugriff auf unsere Webseiten werden folgende Daten in Logfiles gespeichert: IP-Adresse, Datum, Uhrzeit, Browser-Anfrage und allg. übertragene Informationen zum Betriebssystem resp. Browser. Diese Nutzungsdaten bilden die Basis für statistische, anonyme Auswertungen, so dass Trends erkennbar sind, anhand derer wir unsere Angebote entsprechend verbessern können.';
	$printPart($part, $text);
}
if ($Cont->SET['Einblenden']['Cookies']->v) {
	$part = 'Cookies';
	$text = 'Zur Erleichterung der Nutzung unserer Webseite setzen wir Cookies ein. Cookies sind Dateien, die auf der Festplatte Ihres Computers abgelegt werden und zur Vereinfachung der Navigation und Interaktion dienen. Sie können das Speichern von Cookies auf Ihrer Festplatte durch die Aktivierung bestimmter Einstellungen auf Ihrem Browser beeinflussen oder teilweise bzw. vollständig verhindern.';
	$printPart($part, $text);
}
if ($Cont->SET['Einblenden']['Datenschutz: Facebook']->v) {
	$part = 'Datenschutz: Facebook';
	$text = 'Auf unseren Seiten sind Plugins des sozialen Netzwerks Facebook, 1601 South California Avenue, Palo Alto, CA 94304, USA integriert. Die Facebook-Plugins erkennen Sie an dem Facebook-Logo oder dem "Like-Button" ("Gefällt mir") auf unserer Seite. Eine Übersicht über die Facebook-Plugins finden Sie hier: <a href="http://developers.facebook.com/docs/plugins/" target="_blank">http://developers.facebook.com/docs/plugins/</a>.
	Wenn Sie unsere Seiten besuchen, wird über das Plugin eine direkte Verbindung zwischen Ihrem Browser und dem Facebook-Server hergestellt. Facebook erhält dadurch die Information, dass Sie mit Ihrer IP-Adresse unsere Seite besucht haben. Wenn Sie den Facebook "Like-Button" anklicken während Sie in Ihrem Facebook-Account eingeloggt sind, können Sie die Inhalte unserer Seiten auf Ihrem Facebook-Profil verlinken. Dadurch kann Facebook den Besuch unserer Seiten Ihrem Benutzerkonto zuordnen. Wir weisen darauf hin, dass wir als Anbieter der Seiten keine Kenntnis vom Inhalt der übermittelten Daten sowie deren Nutzung durch Facebook erhalten. Weitere Informationen hierzu finden Sie in der Datenschutzerklärung von facebook unter <a href="https://www.facebook.com/about/privacy/" target="_blank">https://www.facebook.com/about/privacy/</a>
	Wenn Sie nicht wünschen, dass Facebook den Besuch unserer Seiten Ihrem Facebook-Nutzerkonto zuordnen kann, loggen Sie sich bitte aus Ihrem Facebook-Benutzerkonto aus.';
	$printPart($part, $text);
}
if ($Cont->SET['Einblenden']['Datenschutz: Twitter']->v) {
	$part = 'Datenschutz: Twitter';
	$text = 'Auf unseren Seiten sind Funktionen des Dienstes Twitter eingebunden. Diese Funktionen werden angeboten durch die Twitter Inc., 795 Folsom St., Suite 600, San Francisco, CA 94107, USA. Durch das Benutzen von Twitter und der Funktion "Re-Tweet" werden die von Ihnen besuchten Webseiten mit Ihrem Twitter-Account verknüpft und anderen Nutzern bekannt gegeben. Dabei werden u.a. Daten wie IP-Adresse, Browsertyp, aufgerufene Domains, besuchte Seiten, Mobilfunkanbieter, Geräte- und Applikations-IDs und Suchbegriffe an Twitter übertragen.
	Wir weisen darauf hin, dass wir als Anbieter der Seiten keine Kenntnis vom Inhalt der übermittelten Daten sowie deren Nutzung durch Twitter erhalten. Aufgrund laufender Aktualisierung der Datenschutzerklärung von Twitter, weisen wir auf die aktuellste Version unter (<a href="http://twitter.com/privacy" target="_blank">http://twitter.com/privacy</a>) hin.
	Ihre Datenschutzeinstellungen bei Twitter können Sie in den Konto-Einstellungen unter <a href="http://twitter.com/account/settings" target="_blank">http://twitter.com/account/settings</a> ändern. Bei Fragen wenden Sie sich an <a href="mailto:privacy@twitter.com">privacy@twitter.com</a>.';
	$printPart($part, $text);
}
if ($Cont->SET['Einblenden']['Datenschutz: Google Adsense']->v) {
	$part = 'Datenschutz: Google Adsense';
	$text = 'Diese Website benutzt Google AdSense, einen Dienst zum Einbinden von Werbeanzeigen der Google Inc. ("Google"). Google AdSense verwendet sog. "Cookies", Textdateien, die auf Ihrem Computer gespeichert werden und die eine Analyse der Benutzung der Website ermöglicht. Google AdSense verwendet auch so genannte Web Beacons (unsichtbare Grafiken). Durch diese Web Beacons können Informationen wie der Besucherverkehr auf diesen Seiten ausgewertet werden.
	Die durch Cookies und Web Beacons erzeugten Informationen über die Benutzung dieser Website (einschließlich Ihrer IP-Adresse) und Auslieferung von Werbeformaten werden an einen Server von Google in den USA übertragen und dort gespeichert. Diese Informationen können von Google an Vertragspartner von Google weiter gegeben werden. Google wird Ihre IP-Adresse jedoch nicht mit anderen von Ihnen gespeicherten Daten zusammenführen.
	Sie können die Installation der Cookies durch eine entsprechende Einstellung Ihrer Browser Software verhindern; wir weisen Sie jedoch darauf hin, dass Sie in diesem Fall gegebenenfalls nicht sämtliche Funktionen dieser Website voll umfänglich nutzen können. Durch die Nutzung dieser Website erklären Sie sich mit der Bearbeitung der über Sie erhobenen Daten durch Google in der zuvor beschriebenen Art und Weise und zu dem zuvor benannten Zweck einverstanden.';
	$printPart($part, $text);
}
if ($Cont->SET['Einblenden']['Datenschutz: Google Analytics']->v) {
	$part = 'Datenschutz: Google Analytics';
	$text = 'Diese Website benutzt Google Analytics, einen Webanalysedienst der Google Inc. ("Google"). Google Analytics verwendet sog. "Cookies", Textdateien, die auf Ihrem Computer gespeichert werden und die eine Analyse der Benutzung der Website durch Sie ermöglichen. Die durch den Cookie erzeugten Informationen über Ihre Benutzung dieser Website werden in der Regel an einen Server von Google in den USA übertragen und dort gespeichert. Im Falle der Aktivierung der IP-Anonymisierung auf dieser Webseite wird Ihre IP-Adresse von Google jedoch innerhalb von Mitgliedstaaten der Europäischen Union oder in anderen Vertragsstaaten des Abkommens über den Europäischen Wirtschaftsraum zuvor gekürzt.
	Nur in Ausnahmefällen wird die volle IP-Adresse an einen Server von Google in den USA übertragen und dort gekürzt. Google wird diese Informationen benutzen, um Ihre Nutzung der Website auszuwerten, um Reports über die Websiteaktivitäten für die Websitebetreiber zusammenzustellen und um weitere mit der Websitenutzung und der Internetnutzung verbundene Dienstleistungen zu erbringen. Auch wird Google diese Informationen gegebenenfalls an Dritte übertragen, sofern dies gesetzlich vorgeschrieben oder soweit Dritte diese Daten im Auftrag von Google verarbeiten. Die im Rahmen von Google Analytics von Ihrem Browser übermittelte IP-Adresse wird nicht mit anderen Daten von Google zusammengeführt.
	Sie können die Installation der Cookies durch eine entsprechende Einstellung Ihrer Browser Software verhindern; wir weisen Sie jedoch darauf hin, dass Sie in diesem Fall gegebenenfalls nicht sämtliche Funktionen dieser Website voll umfänglich nutzen können. Durch die Nutzung dieser Website erklären Sie sich mit der Bearbeitung der über Sie erhobenen Daten durch Google in der zuvor beschriebenen Art und Weise und zu dem zuvor benannten Zweck einverstanden.';
	$printPart($part, $text);
}
if ($Cont->SET['Einblenden']['Datenschutz: Google Plus']->v) {
	$part = 'Datenschutz: Google Plus';
	$text = 'Mithilfe der Google +1-Schaltfläche können Sie Informationen weltweit veröffentlichen. Über die Google +1-Schaltfläche erhalten Sie und andere Nutzer personalisierte Inhalte von Google und dessen Partnern. Google speichert sowohl die Information, dass Sie für einen Inhalt +1 gegeben haben, als auch Informationen über die Seite, die Sie beim Klicken auf +1 angesehen haben. Ihre +1 können als Hinweise zusammen mit Ihrem Profilnamen und Ihrem Foto in Google-Diensten, wie etwa in Suchergebnissen oder in Ihrem Google-Profil, oder an anderen Stellen auf Websites und Anzeigen im Internet eingeblendet werden.
	Google zeichnet Informationen über Ihre +1-Aktivitäten auf, um die Google-Dienste für Sie und andere zu verbessern.
	Um die Google +1-Schaltfläche verwenden zu können, benötigen Sie ein weltweit sichtbares, öffentliches Google-Profil, das zumindest den für das Profil gewählten Namen enthalten muss. Dieser Name wird in allen Google-Diensten verwendet. In manchen Fällen kann dieser Name auch einen anderen Namen ersetzen, den Sie beim Teilen von Inhalten über Ihr Google-Konto verwendet haben. Die Identität Ihres Google-Profils kann Nutzern angezeigt werden, die Ihre E-Mail-Adresse kennen oder über andere identifizierende Informationen von Ihnen verfügen.
	Neben den oben erläuterten Verwendungszwecken werden die von Ihnen bereitgestellten Informationen gemäß den geltenden Google-Datenschutzbestimmungen (<a href="http://www.google.com/intl/de/policies/privacy/" "target="_blank">http://www.google.com/intl/de/policies/privacy/</a>) genutzt. Google veröffentlicht möglicherweise zusammengefasste Statistiken über die +1-Aktivitäten der Nutzer bzw. geben diese Statistiken an unsere Nutzer und Partner weiter, wie etwa Publisher, Inserenten oder verbundene Websites.';
	$printPart($part, $text);
}
if ($Cont->SET['Einblenden']['Datenschutz: Google Remarketing']->v) {
	$part = 'Datenschutz: Google Remarketing';
	$text = 'Diese Website verwendet die Remarketing- bzw. "Ähnliche Zielgruppen"-Funktion der Google Inc. ("Google"). Sie können so zielgerichtet mit Werbung angesprochen werden, indem personalisierte und interessenbezogene Anzeigen geschaltet werden, wenn Sie andere Webseiten im sog. "Google Display-Netzwerk" besuchen. "Google Remarketing" bzw. die Funktion "Ähnliche Zielgruppen" verwendet dafür sog. "Cookies", Textdateien, die auf Ihrem Computer gespeichert werden und die eine Analyse der Benutzung der Website durch Sie ermöglichen. Über diese Textdateien werden Ihre Besuche sowie anonymisierte Daten über die Nutzung der Website erfasst. Personenbezogene Daten werden dabei nicht gespeichert. Besuchen Sie eine andere Webseite im sog. "Google Display-Netzwerk" werden Ihnen ggf. Werbeeinblendungen angezeigt, die mit hoher Wahrscheinlichkeit zuvor auf unserer Website aufgerufene Produkt- und Informationsbereiche berücksichtigen. Sie können die Installation der Cookies durch eine entsprechende Einstellung Ihrer Browser Software verhindern; wir weisen Sie jedoch darauf hin, dass Sie in diesem Fall gegebenenfalls nicht sämtliche Funktionen dieser Website voll umfänglich nutzen können. Durch die Nutzung dieser Website erklären Sie sich mit der Bearbeitung der über Sie erhobenen Daten durch Google in der zuvor beschriebenen Art und Weise und zu dem zuvor benannten Zweck einverstanden.';
	$printPart($part, $text);
}
