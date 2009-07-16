<?php

class AllanMcController extends BaseController
{
  /**
   * Declares class-based actions.
   */
  public function actions()
  {
    return array(
    );
  }

  /**
   * This is the default 'index' action that is invoked
   * when an action is not explicitly requested by users.
   */
  public function actionIndex()
  {
    $this->layout = 'ajax';
    $this->render('index');
  }

  /**
   * Displays the contact page
   */
  public function actionContact()
  {
    $contact=new ContactForm;
    if(isset($_POST['ContactForm']))
    {
      $contact->attributes=$_POST['ContactForm'];
      if($contact->validate())
      {
        $headers="From: {$contact->email}\r\nReply-To: {$contact->email}";
        mail(Yii::app()->params['adminEmail'],$contact->subject,$contact->body,$headers);
        Yii::app()->user->setFlash('contact','Thank you for contacting us. We will respond to you as soon as possible.');
        $this->refresh();
      }
    }
    $this->render('contact',array('contact'=>$contact));
  }

  public function actionErrors()
  {
    if($errors = Yii::app()->errorHandler->error)
      $this->render('error', $error);
    else
      throw new CHttpException('404', 'The specified page could not be found');
  }

  /**
   * Displays the login page
   */
  public function actionLogin()
  {
    $form=new LoginForm;
    // collect user input data
    if(isset($_POST['LoginForm']))
    {
      $form->attributes=$_POST['LoginForm'];
      // validate user input and redirect to previous page if valid
      if($form->validate())
        $this->redirect(Yii::app()->user->returnUrl);
    }
    // display the login form
      $this->layout = 'ajax';
    $this->render('login',array('form'=>$form));
  }

  /**
   * Logout the current user and redirect to homepage.
   */
  public function actionLogout()
  {
    Yii::app()->user->logout();
    $this->redirect(Yii::app()->homeUrl);
  }

}
