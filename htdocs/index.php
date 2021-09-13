<?php
// phpcs:disable Generic.Arrays.DisallowLongArraySyntax
// @see https://github.com/u01jmg3/ics-parser
require_once '../vendor/autoload.php';

use ICal\ICal;
use Carbon\Carbon;

/**
* Récupérer un calendrier Google :
** Paramètres
** Choisir son calendrier
** "Partager avec des personnes en particulier"
** "Adresse secrète au format iCal"
*/
$calendars = [
    'velocite'  => 'http://www.velocite-montpellier.fr/?plugin=all-in-one-event-calendar&controller=ai1ec_exporter_controller&action=export_events',
];
$ical = [];
foreach($calendars as $name => $calendar){
    $filename = dirname(__DIR__).'/data/'.$name.'.ics';
    if(!file_exists($filename) || filemtime($filename) - time() > 3600){
        if(($handle = fopen($filename, 'w')) !== false){
            $data = file_get_contents($calendar);
            fwrite($handle, $data);
            fclose($handle);
        }
    }
    if(file_exists($filename)){
        try {
            $ical[] = new ICal($filename, [
                'defaultSpan'                 => 2,     // Default value
                'defaultTimeZone'             => 'UTC',
                'defaultWeekStart'            => 'MO',  // Default value
                'disableCharacterReplacement' => false, // Default value
                'filterDaysAfter'             => null,  // Default value
                'filterDaysBefore'            => null,  // Default value
                'skipRecurrence'              => false, // Default value
            ]);
            // $ical->initFile('ICal.ics');
            // $ical->initUrl('https://raw.githubusercontent.com/u01jmg3/ics-parser/master/examples/ICal.ics', $username = null, $password = null, $userAgent = null);
        } catch (\Exception $e) {
            die($e);
        }
    }
}
$items = [];
if(is_array($ical) && count($ical) > 0){
    foreach($ical as $calendar){
        $events = $calendar->eventsFromInterval('3 week');
        foreach($events as $event){
            $date = Carbon::createFromTimestamp($event->dtstart_array[2]);
            $event->date = $date->format('d/m/Y H:i');
            $items[$event->dtstart.'-'.uniqid()] = $event;
        }
    }
}
ksort($items);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <title>Calendrier Vélo Montpellier</title>
    <style>body { background-color: #eee } .caption { overflow-x: auto }</style>
</head>
<body>
<div class="container-fluid">
    <h1>Calendrier Vélo Montpellier</h1>
    <?php if(is_array($items) && count($items) > 0): ?>
        <?php foreach($items as $item): ?>
            <div class="row">
                <h2><?php echo $item->summary ?></h2>
                <h3><?php echo $item->date ?></h3>
            </div>
        <?php endforeach ?>
    <?php endif ?>
</div>
</body>
</html>
