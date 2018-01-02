<!DOCTYPE html>
<html>
    <body>
<?php

// Used namespaces
use CyrilPerrin\Form\Form;
use CyrilPerrin\Form\Field\Date;
use CyrilPerrin\Form\Field\File;
use CyrilPerrin\Form\Field\Submit;
use CyrilPerrin\Form\Field\File\Image;
use CyrilPerrin\Form\Field\Input\Hidden;
use CyrilPerrin\Form\Field\Input\Select;
use CyrilPerrin\Form\Field\Input\Text;

// Require autoload
require('autoload.php');

// Add vendor directory to include path
set_include_path(get_include_path().PATH_SEPARATOR.__DIR__.'/../vendor');

// Use
$form = new Form();
$firstname = $form->add(new Text('firstname', 'Firstname'));
$lastname = $form->add(new Text('lastname', 'Lastname'));
$birthday = $form->add(
    new Date('birthday', 'Birthday', null, null, time())
);
$genders = array('male','female');
$gender = $form->add(
    new Select(
        'gender', $genders, 'Gender', false, false
    )
);
$email = $form->add(new Text('email', 'Email'));
$email->setCallback(
    function (Text $email) {
        return filter_var($email->getValue(), FILTER_VALIDATE_EMAIL) !== false;
    }
);
$blog = $form->add(new Text('blog', 'Blog'));
$blog->setCallback(
    function (Text $website) {
        return filter_var($website->getValue(), FILTER_VALIDATE_URL) !== false;
    }
);
$form->add(new Submit('submit', 'Submit'));
$start = $form->add(new Hidden('start', time()));
$resume = $form->add(
    new File(
        'resume', 'Resume', null, null, array('odt','doc','docx','pdf','txt')
    ), false
);
$avatar = $form->add(
    new Image(
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
            '<li>Gender : ',$genders[$gender->getValue()],'</li>',
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