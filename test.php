<html>
    <head>
        <link rel="stylesheet" href="../../pukkila.com_dev/style/screen.css" />
        <!--link rel="stylesheet" href="../../mehukatti.com_dev/synttarivuosi/style/base.css" /-->
    </head>
    <body>

<?php
    
require_once('AutoCRUD.php');

$c = new AutoCRUD('tiedotteet',
    array(
        'disabled' => array('kuva'),
        'hidden'   => array(),
        'select'   => array('alue'      => array('Pohjois-Savo', 'Uusimaa', 'Varsinais-suomi'),
                            'kategoria' => array('Yritys', 'Yhteystyö', 'Tuoteuutuudet')),
        'radio'    => array('pukkila'   => array('0' => 'ei', '1' => 'kyllä'), 'published' => array('0' => 'ei', '1' => 'julkaistu')),
        'perPage'  => 15,
        'dateFormat'     => 'd.m.Y',
        'timeFormat'     => 'H:i:s',
        //'listing'  => array('id', 'edited', 'otsikko'),
        'upload'   => array('kuva' => array('target' => 'uploads/', 'max_size' => 1000000, 'allowed' => array('jpg', 'gif', 'png')))
        //,        'convert'  => array('edited' => 'convertDate')
    )
);

function convertDate($string) {
    return empty($string) || $string == '0000-00-00 00:00:00' ? $string : date("d.m.Y H:i:s", strtotime($string));
}

?>
</body>
</html>