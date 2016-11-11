<?php
require_once "../../config.php";

use \Tsugi\Core\LTIX;

// Retrieve the launch data if present
$LTI = LTIX::requireData();
$p = $CFG->dbprefix;
$displayname = $USER->displayname;


echo('<span style="float: right; margin-bottom: 10px;">');
if ( $USER->instructor ) {
    echo('<a href="convert.php"><button class="btn btn-info">Convert Moodle XML</button></a> '."\n");}
    echo('<a href="index.php"><button class="btn btn-info active">My Assignments</button></a> '."\n");
$OUTPUT->exitButton();
echo('</span>');




// Retrieve the old data
$row = $PDOX->rowDie("SELECT guess FROM {$p}tsugi_sample_module
    WHERE user_id = :UI",
    array(':UI' => $USER->id)
);
$oldguess = $row ? $row['guess'] : '';

// Start of the output
$OUTPUT->header();
$OUTPUT->bodyStart();
$OUTPUT->flashMessages();




$OUTPUT->footerStart();
?>
<script>
// You might put some JavaScript here
</script>
<?php
$OUTPUT->footerEnd();

