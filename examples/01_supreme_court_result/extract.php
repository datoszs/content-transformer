<?php
use DatosCZ\Transformer\Content\FileContent;
use DatosCZ\Transformer\Gears\CastToHTML;
use DatosCZ\Transformer\Gears\ContentStater;
use DatosCZ\Transformer\Gears\FileReader;
use DatosCZ\Transformer\Gears\Finalize;
use DatosCZ\Transformer\Gears\RegexExtract;
use DatosCZ\Transformer\Gears\RegexMatch;
use DatosCZ\Transformer\Gears\RegexSplit;
use DatosCZ\Transformer\Gears\StripHTML;
use DatosCZ\Transformer\State\State;
use DatosCZ\Transformer\Utils\GearBox;

include __DIR__ . '/../autoload.php';


// Definitions of content transformer to extract case result
$gearbox = new GearBox();
$gearbox->addGear(new FileReader("LOAD_FILE_CONTENT"));
$gearbox->addGear(new CastToHTML("PRETYPUJ_NA_HTML"));
$gearbox->addGear(new StripHTML("PREVED_DO_TEXTU"));
$gearbox->addGear(new RegexSplit("VYTAHNOUT_ROZHODNUTI", '/takto/', RegexSplit::RIGHT));
$gearbox->addGear(new RegexSplit("OREZAT_HLAVICKU", '/(Odůvodnění|odůvodnění|(o d ů v o d n ě n í)|(O d ů v o d n ě n í))/', RegexSplit::LEFT));
$gearbox->addGear(new RegexMatch("SE_ODMITA", '/ odmítá/'));
$gearbox->addGear(new RegexMatch("SE_ZASTAVUJE", '/ zastavuje/'));

// For case "odmítnut"
$gearbox->addGear(new RegexMatch("MA_RIMSKA_DVA", '/II\. /'));
$gearbox->addGear(new RegexMatch("MA_RIMSKA_TRI", '/III\. /'));
$gearbox->addGear(new RegexSplit("ROZDEL_NA_TRI", '/III\. /', RegexSplit::LEFT));

$gearbox->addGear(new RegexMatch("MA_NAHRAD", '/náhrad/'));

// For case "zamítnut"
$gearbox->addGear(new RegexMatch("MA_RIMSKA_DVA_2", '/II\. /'));
$gearbox->addGear(new RegexMatch("MA_RIMSKA_TRI_2", '/III\. /'));
$gearbox->addGear(new RegexSplit("ROZDEL_NA_TRI_2", '/III\. /', RegexSplit::LEFT));

$gearbox->addGear(new RegexMatch("MA_NAHRAD_2", '/náhrad/'));

// Tagging result
$gearbox->addGear(new ContentStater("NEGATIVE", 'NEGATIVE'));
$gearbox->addGear(new ContentStater("POSITIVE", 'POSITIVE'));
$gearbox->addGear(new ContentStater("NEUTRAL", 'NEUTRAL'));

$gearbox->addGear(new Finalize('FINALIZACE'));

$gearbox->setStart("LOAD_FILE_CONTENT");
$gearbox->addTransition("LOAD_FILE_CONTENT", FileReader::LOADED, "PRETYPUJ_NA_HTML");
$gearbox->addTransition("PRETYPUJ_NA_HTML", CastToHTML::CASTED, "PREVED_DO_TEXTU");
$gearbox->addTransition("PREVED_DO_TEXTU", StripHTML::STRIPPED, "VYTAHNOUT_ROZHODNUTI");
$gearbox->addTransition("VYTAHNOUT_ROZHODNUTI", RegexSplit::FOUND, "OREZAT_HLAVICKU");
$gearbox->addTransition("OREZAT_HLAVICKU", RegexSplit::FOUND, "SE_ODMITA");
$gearbox->addTransition("SE_ODMITA", RegexMatch::MATCHED, "MA_RIMSKA_DVA");
$gearbox->addTransition("SE_ODMITA", RegexMatch::NOT_MATCHED, "SE_ZASTAVUJE");

$gearbox->addTransition("MA_RIMSKA_DVA", RegexMatch::NOT_MATCHED, "NEGATIVE");
$gearbox->addTransition("MA_RIMSKA_DVA", RegexMatch::MATCHED, "MA_RIMSKA_TRI");
$gearbox->addTransition("MA_RIMSKA_TRI", RegexMatch::NOT_MATCHED, "MA_NAHRAD");
$gearbox->addTransition("MA_RIMSKA_TRI", RegexMatch::MATCHED, "ROZDEL_NA_TRI");
$gearbox->addTransition("MA_NAHRAD", RegexMatch::MATCHED, "NEGATIVE");
$gearbox->addTransition("MA_NAHRAD", RegexMatch::NOT_MATCHED, "POSITIVE");
$gearbox->addTransition("ROZDEL_NA_TRI", RegexSplit::FOUND, "MA_NAHRAD");

$gearbox->addTransition("SE_ZASTAVUJE", RegexMatch::MATCHED, "MA_RIMSKA_DVA_2");
$gearbox->addTransition("SE_ZASTAVUJE", RegexMatch::NOT_MATCHED, "POSITIVE");

$gearbox->addTransition("MA_RIMSKA_DVA_2", RegexMatch::NOT_MATCHED, "NEUTRAL");
$gearbox->addTransition("MA_RIMSKA_DVA_2", RegexMatch::MATCHED, "MA_RIMSKA_TRI_2");
$gearbox->addTransition("MA_RIMSKA_TRI_2", RegexMatch::MATCHED, "ROZDEL_NA_TRI_2");
$gearbox->addTransition("MA_RIMSKA_TRI_2", RegexMatch::NOT_MATCHED, "MA_NAHRAD_2");
$gearbox->addTransition("ROZDEL_NA_TRI_2", RegexSplit::FOUND, "MA_NAHRAD_2");
$gearbox->addTransition("MA_NAHRAD_2", RegexMatch::MATCHED, "NEUTRAL");
$gearbox->addTransition("MA_NAHRAD_2", RegexMatch::NOT_MATCHED, "POSITIVE");

$gearbox->addTransition("POSITIVE", ContentStater::DONE, "FINALIZACE");
$gearbox->addTransition("NEUTRAL", ContentStater::DONE, "FINALIZACE");
$gearbox->addTransition("NEGATIVE", ContentStater::DONE, "FINALIZACE");


// Content transformer for obtaining registry mark
$gearbox2 = new GearBox();
$gearbox2->addGear(new FileReader("LOAD_FILE_CONTENT"));
$gearbox2->addGear(new CastToHTML("PRETYPUJ_NA_HTML"));
$gearbox2->addGear(new StripHTML("PREVED_DO_TEXTU"));
$gearbox2->addGear(new RegexExtract('EXTRACT_MARK', '/(Spisová|Senátní) značka:\n\s*(.*?)\n\s*ECLI/', 2));
$gearbox2->addGear(new Finalize('FINALIZACE'));

$gearbox2->setStart('LOAD_FILE_CONTENT');
$gearbox2->addTransition("LOAD_FILE_CONTENT", FileReader::LOADED, "PRETYPUJ_NA_HTML");
$gearbox2->addTransition("PRETYPUJ_NA_HTML", CastToHTML::CASTED, "PREVED_DO_TEXTU");
$gearbox2->addTransition("PREVED_DO_TEXTU", CastToHTML::CASTED, "EXTRACT_MARK");
$gearbox2->addTransition("EXTRACT_MARK", RegexMatch::MATCHED, "FINALIZACE");


$output = [];
$dir = '/Users/jan/Projekty/DATOS/2016-ns/documents';
$dh = opendir($dir);

// For loop on files in given dir
while (false !== ($filename = readdir($dh))) {
    if ($filename == '.' || $filename == '..') {
        continue;
    }

    $fileContent = new FileContent($dir . '/' . $filename);

    // Extract registry mark
    $state = new State($fileContent);
    $gearbox2->process($state);
    if ($state->isFinalized()) {
        $registryMark = $state->getContent()->get();
    }

    // Extract result
    $state = new State($fileContent);
    $gearbox->process($state);
    if ($state->isFinalized()) {
        $result = $state->getContent()->get();
    }
    $error = $state->getErrorState();

    $temp = new stdClass();
    $temp->filename = $filename;
    $temp->registryMark = '"' . ($registryMark ?? null) . '"';
    $temp->result = $result ?? null;
    $temp->error = $error;

    $output[] = $temp;
    print implode('; ', (array) $temp) . "\n";
}

