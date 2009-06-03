<?php

class configuration extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return CActiveRecord the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'configuration';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('client','length','max'=>32),
			array('downloadDir','length','max'=>256),
			array('fileExtension','length','max'=>16),
			array('watchDir','length','max'=>256),
			array('matchStyle, onlyNewer, saveFile', 'numerical', 'integerOnly'=>true),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'=>'Id',
			'client'=>'Download Client',
			'downloadDir'=>'Download Directory',
			'fileExtension'=>'File Extension',
			'matchStyle'=>'Matching Style',
			'onlyNewer'=>'Only Newer Episodes',
			'saveFile'=>'Save File',
			'watchDir'=>'Watch Directory',
		);
	}
}
