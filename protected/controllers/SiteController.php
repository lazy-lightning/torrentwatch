<?php

class SiteController extends BaseController
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image
			// this is used by the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xEBF4FB,
			),
		);
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
    $items = $this->prepareListItems(array(
          array('label'=>'Feed Items', 'url'=>array('feed/list')),
          array('label'=>'TV Shows', 'url'=>array('tvShow/list')),
          array('label'=>'Movies', 'url'=>array('movie/list')),
          array('label'=>'Other', 'url'=>array('other/list')),
    ));
		$this->render('index_'.$this->resolution,array('items'=>$items, 'firstLine'=>$items[0]['index']));
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

  /**
   * This is the action called from the sidebar to list
   * the types of favorites available.
   */
  public function actionFavorites()
  {
    $items = $this->prepareListItems(array(
          array('label'=>'Tv Shows', 'url'=>array('favoriteTvShows/list')),
          array('label'=>'Movies', 'url'=>array('favoriteMovies/list')),
          array('label'=>'String Matching', 'url'=>array('favoriteStrings/list')),
    ));
		$this->render('index_'.$this->resolution,array('items'=>$items, 'firstLine'=>$items[0]['index']));
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
