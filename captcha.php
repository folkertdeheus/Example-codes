<?php
    /**
     * 
     * reCaptcha v3 voorbeeld
     * 
     * 1. Ga naar https://www.google.com/recaptcha/admin/create om een "keypair" voor de website aan te maken
     * 2. Als het gelukt is heb je een SiteKey en een SecretKey
     * 3. Vervang in de code "SITEKEY" voor de SiteKey, en "SECRETKEY" voor de SecretKey
     *      Mocht je dit in verschillende documenten opsplitsen, dan kan het handiger zijn om de siteKey rechtstreeks in het javascript deel toe te voegen
     *      Vervang daar <?= $sitekey; ?> voor de SiteKey, je kunt in het php deel dan $siteKey = 'SITEKEY' weghalen (de secretKey laten staan!)
     * 
     */

    $siteKey = 'SITEKEY';
    $secretKey = 'SECRETKEY';
  
    /**
     *
     * Hier wordt de data naar de servers van google gestuurd om te verifiëren
     *  
     */
    
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = array('secret' => $secretKey, 'response' => $_POST['captcha']);
  
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        )
    );

    $context  = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    $responseKeys = json_decode($response,true);
  
    /**
     * 
     * Dit is de laatste stap:
     * $responseKeys["success"] true/false geeft aan of de token die was gestuurd geaccepteerd is
     * 
     * Score filteren:
     * $responseKeys["score"] geeft een schaal van 0.0 tot 1.0 hoe waarschijnlijk het is dat je met een mens te maken hebt
     * - 0.0 is heel waarschijnlijk een bot
     * - 1.0 is heel waarschijnlijk een mens
     * 
     */


    // Check of de controle van reCaptcha is uitgevoerd
    if($responseKeys["success"]) {

        // Check aan de hand van de score of het formulier door een bot of mens is ingevuld
        // Als er teveel gefilterd wordt (en dus ook door een mens ingevulde formulieren worden geweigerd), dan kan de score naar beneden

        if ($responseKeys["score"] >= 0.5) {
            // Waarschijnlijk een gebruiker
            // Hier doorgaan met de normale afhandeling
        } else {
            // Waarschijnlijk een bot
        }
        
    } else {
        // Controle is niet uitgevoerd
    }

?>

<html lang="en">
<head>
    <meta charset="utf-8">

    <title>reCaptcha Test</title>
<!--
    Beide documenten zijn nodig voor het functioneren
    - Het google reCaptcha script
    - jQuery voor het toevoegen van de reCaptcha token
    -->
<script src="https://www.google.com/recaptcha/api.js?render=6Leah8oUAAAAAMTy0OMhJxk46TjPGPhkoDvLENq_"></script>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
</head>

<body>

<script>
    /**
        Javascript en jQuery om het verzenden van het formulier aan te passen
        - De actie van het formulier verzenden wordt onderschept en gestopt
        - Vraag een reCaptcha-token aan, hier wordt gecheckt of de handelingen die gedaan zijn op de website overeenkomen met die van een normale gebruiker, of die van een bot
        - Voeg een "hidden" veld toe aan het formulier, waarin de token verstuurd wordt
        - Verzend het formulier
     */

    // Laad het script pas als het hele document is geladen
    $(document).ready(function() {

        // Actie die wordt uitgevoerd wanneer het formulier met id="captcha_test_form" wordt verzonden
        // Veranderd "captcha_test_form" voor het id van het te gebruiken formulier
        $('#captcha_test_form').submit(function() {
            
            // Voorkom dat het formulier verzonden wordt
            event.preventDefault();
            
            // Pas doorgaan als reCaptcha geladen is
            grecaptcha.ready(function() {
                
                // Vraag een reCaptcha token aan
                // Hier moet de SiteKey ingevuld worden
                // "action:" kan verschillende waarden hebben: zie https://developers.google.com/recaptcha/docs/v3 voor de verschillende opties
                grecaptcha.execute('<?= $siteKey; ?>', {action: 'homepage'}).then(function(token) {
                    
                    // Voeg de token toe aan de formuliervelden
                    $('#captcha_test_form').prepend('<input type="hidden" name="captcha" value="' + token + '">');

                        // Verzend het formulier
                        $('#captcha_test_form').submit();
                    });
                });
            });
        });
    </script>

    <style>
        /* Verberg de reCaptcha badge, die rechts onderin het scherm staat
        Om dit te doen moet je wel op de website vermelden dat je gebruik maakt van reCaptcha
        Zie onderaan het formulier */
        .grecaptcha-badge { visibility: hidden; }
    </style>

<!--
    Voorbeeldformulier met
    id "captcha_test_form"

    Het gebruik van de melding onder aan het formulier is verplicht wanneer je de reCaptcha badge wil verbergen
    -->

    <form method="post" id="captcha_test_form">
    <input type="text" name="test" id="test" />
    <input type="submit" />
    <br>
    This site is protected by reCAPTCHA and the Google
    <a href="https://policies.google.com/privacy">Privacy Policy</a> and
    <a href="https://policies.google.com/terms">Terms of Service</a> apply.

</body>
</html>