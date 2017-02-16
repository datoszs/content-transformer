<?php
use DatosCZ\Transformer\Content\FileContent;
use DatosCZ\Transformer\Gears\CastToHTML;
use DatosCZ\Transformer\Gears\ErrorStater;
use DatosCZ\Transformer\Gears\FileReader;
use DatosCZ\Transformer\Gears\Finalize;
use DatosCZ\Transformer\Gears\RegexExtract;
use DatosCZ\Transformer\Gears\RegexMatch;
use DatosCZ\Transformer\Gears\RegexSplit;
use DatosCZ\Transformer\Gears\StripHTML;
use DatosCZ\Transformer\State\State;
use DatosCZ\Transformer\Utils\GearBox;

include __DIR__ . '/../autoload.php';


$gearbox = new GearBox();
$gearbox->addGear(new FileReader("LOAD_FILE_CONTENT"));
$gearbox->addGear(new CastToHTML("PRETYPUJ_NA_HTML"));
$gearbox->addGear(new StripHTML("PREVED_DO_TEXTU", true));
$gearbox->addGear(new RegexSplit("VYTAHNOUT_ZAHLAVI", '/takto/', RegexSplit::LEFT));
$gearbox->addGear(new RegexMatch('STRANA_DOVOLANI', '/o dovolání(ch)? (žalovan|(zástavního )?dlužníka|povinn)/'));
$gearbox->addGear(new RegexMatch('DOVOLANI_ZALOBCE', '/o dovolání(ch)? (žalob|věřitel|oprávněné(ho)?)/'));
$gearbox->addGear(new RegexMatch('DOVOLANI_NAVRHOVATEL', '/o dovolání(ch)? navrhovatel(e|ky|ů)/'));

$gearbox->addGear(new RegexSplit('ROZDELENI_UCASTNIKU', '/za účasti/', RegexSplit::LEFT));
$gearbox->addGear(new RegexSplit('ROZDELENI_VPRAVO', '/proti (žalovan|povinn|(zástavnímu )?dlužník(ovi|u))[^\\s]*?\s/', RegexSplit::RIGHT));
$gearbox->addGear(new RegexSplit('ROZDELENI_VLEVO', '/proti (žalovan|povinn|(zástavnímu )?dlužník(ovi|u))[^\\s]*?\s/', RegexSplit::LEFT));
$gearbox->addGear(new RegexExtract('EXTRAKCE', '/zasto[^\\s]*? (.*?),/', 1));
$gearbox->addGear(new RegexMatch('MA_ADVOKATA', '/advokát/'));
$gearbox->addGear(new RegexMatch('DAVA_SMYSL', '/^(([^\s]+)(\s+|$)){2,7}$/'));
$gearbox->addGear(new ErrorStater('NEMA_SMYSL', 'NOT MEANINGFUL'));
$gearbox->addGear(new ErrorStater('NEMA_ADVOKATA', 'NO ADVOCATE'));
$gearbox->addGear(new ErrorStater('FAILED_DOVOLANI_ZALOBCE', 'FAILED_DOVOLANI_ZALOBCE'));
$gearbox->addGear(new ErrorStater('FAILED_DOVOLANI_NAVRHOVATEL', 'FAILED_DOVOLANI_NAVRHOVATEL'));
$gearbox->addGear(new ErrorStater('FAILED_ROZDELENI_VPRAVO', 'FAILED_ROZDELENI_VPRAVO'));
$gearbox->addGear(new ErrorStater('FAILED_ROZDELENI_VLEVO', 'FAILED_ROZDELENI_VLEVO'));
$gearbox->addGear(new Finalize('FINALIZACE'));


// Configure transitions
$gearbox->setStart("LOAD_FILE_CONTENT");
$gearbox->addTransition("LOAD_FILE_CONTENT", FileReader::LOADED, "PRETYPUJ_NA_HTML");
$gearbox->addTransition("PRETYPUJ_NA_HTML", CastToHTML::CASTED, "PREVED_DO_TEXTU");
$gearbox->addTransition("PREVED_DO_TEXTU", StripHTML::STRIPPED, "VYTAHNOUT_ZAHLAVI");
$gearbox->addTransition("VYTAHNOUT_ZAHLAVI", RegexSplit::FOUND, "STRANA_DOVOLANI");
$gearbox->addTransition("STRANA_DOVOLANI", RegexMatch::MATCHED, "ROZDELENI_VPRAVO");
$gearbox->addTransition("STRANA_DOVOLANI", RegexMatch::NOT_MATCHED, "DOVOLANI_ZALOBCE");
$gearbox->addTransition("DOVOLANI_ZALOBCE", RegexMatch::MATCHED, "ROZDELENI_VLEVO");
$gearbox->addTransition("DOVOLANI_ZALOBCE", RegexMatch::NOT_MATCHED, "DOVOLANI_NAVRHOVATEL");
$gearbox->addTransition("DOVOLANI_NAVRHOVATEL", RegexMatch::MATCHED, "ROZDELENI_UCASTNIKU");
$gearbox->addTransition("DOVOLANI_NAVRHOVATEL", RegexMatch::NOT_MATCHED, "FAILED_DOVOLANI_ZALOBCE");
$gearbox->addTransition("ROZDELENI_UCASTNIKU", RegexSplit::FOUND, "EXTRAKCE");
$gearbox->addTransition("ROZDELENI_UCASTNIKU", RegexSplit::NOT_FOUND, "FAILED_DOVOLANI_NAVRHOVATEL");
$gearbox->addTransition("ROZDELENI_VPRAVO", RegexSplit::FOUND, "EXTRAKCE");
$gearbox->addTransition("ROZDELENI_VPRAVO", RegexSplit::NOT_FOUND, "FAILED_ROZDELENI_VPRAVO");
$gearbox->addTransition("ROZDELENI_VLEVO", RegexSplit::FOUND, "EXTRAKCE");
$gearbox->addTransition("ROZDELENI_VLEVO", RegexSplit::NOT_FOUND, "FAILED_ROZDELENI_VLEVO");
$gearbox->addTransition("EXTRAKCE", RegexExtract::EXTRACTED, "DAVA_SMYSL");
$gearbox->addTransition("EXTRAKCE", RegexExtract::NOT_EXTRACTED, "MA_ADVOKATA");
$gearbox->addTransition("DAVA_SMYSL", RegexMatch::MATCHED, "FINALIZACE");
$gearbox->addTransition("DAVA_SMYSL", RegexMatch::NOT_MATCHED, "NEMA_SMYSL");
$gearbox->addTransition("MA_ADVOKATA", RegexMatch::NOT_MATCHED, "NEMA_ADVOKATA");


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
    } else {
        $registryMark = null;
    }

    // Extract result
    $state = new State($fileContent);
    //$gearbox->setDebugMode(true);
    $gearbox->process($state);
    if ($state->isFinalized() && $state->getErrorState() === null) {
        $result = $state->getContent()->get();
    } else {
        $result = null;
    }
    $error = $state->getErrorState();

    $temp = new stdClass();
    $temp->filename = $filename;
    $temp->registryMark = '"' . $registryMark . '"';
    $temp->result = $result;
    $temp->error = $error;

    $output[] = $temp;
    print implode('; ', (array) $temp) . "\n";
}

