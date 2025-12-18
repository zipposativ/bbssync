<?php
  function error_handler(){
    set_error_handler(function($severity, $message, $file, $line) {
      http_response_code(502); // Bad Gateway
      echo "Bad Gateway";
      error_log("PHP Error [$severity] $message in $file on line $line");
      print_r($message." ".$line." ".$file);
      exit;
    });

    // Exception-Handler definieren
    set_exception_handler(function($exception) {
      http_response_code(502); // Bad Gateway
      echo "Bad Gateway";
      error_log("Uncaught Exception: " . $exception->getMessage());
      exit;
    });
  }

  function password(){
    $words = ['einkauf','testlauf','sandelbaum','fenster','kaffee','regen','uhrwerk','papier','garten','brille','stuhl','tisch','lampe','wand','boden','decke','tasse','löffel','gabel','messer','brot','butter','apfel','birne','banane','kirsche','pflaume','orange','zitrone','beere','milch','käse','honig','zucker','salz','pfeffer','nudel','reis','suppe','topf','pfanne','ofen','herd','küche','zimmer','flur','bad','dusche','seife','handtuch','spiegel','bett','kissen','schrank','kommode','regal','buch','heft','stift','papierkorb','tasche','rucksack','jacke','mantel','hose','hemd','schuh','socke','mütze','schal','handschuh','uhr','kalender','wecker','fensterbank','vorhang','tür','schlüssel','klingel','brief','umschlag','marke','paket','kiste','karton','band','schnur','nagel','hammer','zange','schraube','leiter','eimer','besen','lappen','seil','kerze','feuer','asche','rauch','wind','wolke','sonne','mond','stern','himmel','erde','stein','sand','wasser','fluss','see','meer','ufer','boot','rad','wagen','zug','brücke','weg','pfad','platz','park','baum','blatt','zweig','wurzel','blume','gras','wiese','feld','acker','zaun','tor','haus','hof','dach','keller'];

    $needed_words = 3;
    $shuffled = $words;
    shuffle($shuffled);
    $selected = array_slice($shuffled, 0, $needed_words);
    $selected = array_map(
        fn($w) => ucfirst($w),
        $selected
    );
    $number = random_int(0, 100);
    $index = random_int(0, $needed_words - 1);
    $selected[$index] .= $number;
    return implode('-', $selected);
  }
?>
