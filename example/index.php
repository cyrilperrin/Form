<!DOCTYPE html>
<html>
    <body>
<?php

// Used namespaces
use CyrilPerrin\Form\Form;
use CyrilPerrin\Form\Field_Input_Text;
use CyrilPerrin\Form\Field_Input_Select;
use CyrilPerrin\Form\Field_Input_Hidden;
use CyrilPerrin\Form\Field_File;
use CyrilPerrin\Form\Field_File_Image;
use CyrilPerrin\Form\Field_Captcha;
use CyrilPerrin\Form\Field_Date;
use CyrilPerrin\Form\Field_Html;
use CyrilPerrin\Form\Field_Submit;

// Require autoload
require('autoload.php');

// Add vendor directory to include path
set_include_path(get_include_path().PATH_SEPARATOR.__DIR__.'/../vendor');

// Use
$form = new Form();
$firstname = $form->add(new Field_Input_Text('firstname', 'Firstname'));
$lastname = $form->add(new Field_Input_Text('lastname', 'Lastname'));
$birthday = $form->add(
    new Field_Date('birthday', 'Birthday', null, null, time())
);
$gender = $form->add(
    new Field_Input_Select(
        'gender', array('male','female'), 'Gender', false, false
    )
);
$email = $form->add(new Field_Input_Text('email', 'Email'));
$email->setCallback(
    function (Field_Input_Text $email) {
        return filter_var($email->getValue(), FILTER_VALIDATE_EMAIL) !== false;
    }
);
$blog = $form->add(new Field_Input_Text('blog', 'Blog'));
$blog->setCallback(
    function (Field_Input_Text $website) {
        return filter_var($website->getValue(), FILTER_VALIDATE_URL) !== false;
    }
);
$form->add(new Field_Submit('submit', 'Submit'));
$start = $form->add(new Field_Input_Hidden('start', time()));
$resume = $form->add(
    new Field_File(
        'resume', 'Resume', null, null, array('odt','doc','docx','pdf','txt')
    ), false
);
$avatar = $form->add(
    new Field_File_Image(
        'avatar', 'Avatar', null, null, null, null, 100, 100, true
    ), false
);
if ($form->validate()) {
    // Process resume
    if ($resume->isSubmitted()) {
        $resume->moveTo(__DIR__, 'resume', true);
        $resumeLink = '<a href="'.$resume->getFileName().'" target="_blank">Resume</a>';
    } else {
        $resumeLink = 'no resume';
    }
    
    // Process avatar
    if ($avatar->isSubmitted()) {
        $avatar->moveTo(__DIR__, 'avatar', true);
        $avatarImg = '<img src="'.$avatar->getFileName().'" alt="Avatar" />';
    } else {
        $avatarImg = 'no avatar';
    }
    
    // Display fields values
    echo '<h1>Result</h1>',
         '<ul>',
            '<li>Firstname : ',$firstname->getValue(),'</li>',
            '<li>Lastname : ',$lastname->getValue(),'</li>',
            '<li>Birthday : ',date('Y-m-d', $birthday->getTimestamp()),'</li>',
            '<li>Gender : ',$gender->getValue(),'</li>',
            '<li>Email : <a href="mailto:',$email->getValue(),'">',
                $email->getValue(),
            '</a></li>',
            '<li>Blog : <a href="',$blog->getValue(),'">',
                $blog->getValue(),
            '</a></li>',
            '<li>Resume : ',$resumeLink,'</li>',
            '<li>Avatar : ',$avatarImg,'</li>',
            '<li>You have started filling this form at ',
            date('H:i', $start->getValue()),'</li>',
         '</ul>';
}
echo '<h1>Form</h1>',$form;

?>
    </body>
</html>