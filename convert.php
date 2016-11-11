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



//$filename = $_SERVER['DOCUMENT_ROOT']."/tsugi/mod/tsugi-php-module/test.xml";

//$filename = $_SERVER['DOCUMENT_ROOT']."/tsugi/mod/tsugi-php-module/questions-alkene.xml";
$filename = $_SERVER['DOCUMENT_ROOT']."/tsugi/mod/eo_import/questions-jm-zoo-reactants.xml";

$temp = file_get_contents($filename);
 $xmlobj = simplexml_load_string($temp); 

//print_r($xmlobj);

$i=0;
foreach ($xmlobj->question as $question) {

//echo "q=".$i."<br><br><br><br>";
//print_r($question);
//var_dump($question['type']);

echo $question['type'];
var_dump($question->answer);
//echo $question->answer['text'];

echo $question->questiontext->text;

$questiontext = $question->questiontext->text;
$share=1;
$cid = 30;
//echo $question->answer->text;





    if ($question['type'] == 'easyonamejs') {

	$savequery = "INSERT INTO {$p}eo_questions
		(question_id, link_id, user_id, share, category_id, question_type, question_text, created_at, updated_at)
		VALUES (:QID, :LI, :UI, :SHA, :CID, :QTY, :QTX, NOW(), NOW() )
		ON DUPLICATE KEY UPDATE category_id=:CID, question_type=:QTY, share=:SHA, question_text=:QTX, updated_at = NOW()";

            
	    $PDOX->queryDie($savequery,
		array(
		    ':QID' => '',
		    ':LI' => $LINK->id,
		    ':UI' => $USER->id,
		    ':CID' => $cid,
		    ':QTY' => 'structure',
		    ':QTX' => $questiontext,
		    ':SHA' => $share
		)
	    );
            
            $question_id = $PDOX->lastInsertId();

    	$i=1;
    	


	    echo count($question->answer);

	    foreach($question->answer as $answer) {
	    echo $answer->text."<br><br>";
             

	    

            $correct = intval($answer['fraction']/100);
            echo $correct."<br><br>";

            if ($correct == 1) {

            $feedback = "That's correct!  Good work!";
            } else {

            $feedback = "I'm sorry thats incorrect.";
            }

	    $answersmiles = openbabel_convert_molfile($answer->text, 'can');
	    echo $answersmiles;

           


                $savesql = "INSERT INTO {$p}eo_answers
		(answer_id, question_id, correct, answersmiles, answermolfile, answerjson, answerother, feedback, updated_at)
		VALUES (:AID, :QID, :COR, :ANS, :ANM, :ANJ, :ANO, :FBK, NOW())
		ON DUPLICATE KEY UPDATE correct=:COR, answersmiles=:ANS, answermolfile=:ANM, answerjson=:ANJ, answerother=:ANO, feedback=:FBK, updated_at = NOW()";
                   
		   $PDOX->queryDie($savesql,
			array(
			    ':AID' => '',
			    //':AID' => '10',
			    ':QID' => $question_id,
                            ':COR' => $correct,
			    ':ANS' => $answersmiles,
			    ':ANM' => $answer->text,
			    ':ANJ' => '',
			    ':ANO' => '',
			    ':FBK' => $feedback
			)
		    ); 
                     

	    }   //end inner foreach
      }   //end if 

}





/*
if (file_exists($_SERVER['DOCUMENT_ROOT']."/tsugi/mod/tsugi-php-module/test.xml")) {
    $xml = simplexml_load_file($_SERVER['DOCUMENT_ROOT']."/tsugi/mod/tsugi-php-module/test.xml");
 
    print_r($xml);
} else {
    exit('Failed to open test.xml.');
}


//$myxml=simplexml_load_string($xml) or die("Error: Cannot create object");
print_r($xml);
*/


















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




function openbabel_convert_molfile($molfile, $format)
{
    //echo "in fucntion";
    //$marvinjsconfig = get_config('qtype_easyonamejs_options');
    $descriptorspec = array(
        0 => array(
            "pipe",
            "r"
        ), // Stdin is a pipe that the child will read from.
        1 => array(
            "pipe",
            "w"
        ), // Stdout is a pipe that the child will write to.
        2 => array(
            "pipe",
            "r"
        ) // Stderr is a file to write to.
    );
    $output         = '';
    //echo $marvinjsconfig->obabelpath;
    //$command = escapeshellarg($marvinjsconfig->obabelpath . ' -imol -o' . $format . ' --title');
    $command        = '/usr/bin/obabel -imol -o' . $format . ' --title';
    $process        = proc_open($command, $descriptorspec, $pipes);
    //print_object($process);
    if (is_resource($process)) {
        //echo "in if";
        /* 0 => writeable handle connected to child stdin
        1 => readable handle connected to child stdout
        2 +> errors */
        //print_object($pipes);
        //var_dump($pipes);
        fwrite($pipes[0], $molfile);
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $err = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        //echo $output;
        //echo $err;
        // It is important that you close any pipes before calling,
        // proc_close in order to avoid a deadlock.
        $returnvalue = proc_close($process);
        //echo "end if";
    }
    return trim($output);
}
