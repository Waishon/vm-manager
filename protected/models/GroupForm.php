<?php
/*
 * Copyright (C) 2012 FOSS-Group
 *                    Germany
 *                    http://www.foss-group.de
 *                    support@foss-group.de
 *
 * Authors:
 *  Christian Wittkowski <wittkowski@devroom.de>
 *
 * Licensed under the EUPL, Version 1.1 or – as soon they
 * will be approved by the European Commission - subsequent
 * versions of the EUPL (the "Licence");
 * You may not use this work except in compliance with the
 * Licence.
 * You may obtain a copy of the Licence at:
 *
 * https://joinup.ec.europa.eu/software/page/eupl
 *
 * Unless required by applicable law or agreed to in
 * writing, software distributed under the Licence is
 * distributed on an "AS IS" basis,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied.
 * See the Licence for the specific language governing
 * permissions and limitations under the Licence.
 *
 *
 */

class GroupForm extends CFormModel {
	public $dn=null;					/* used for update */
	public $sstDisplayName;
	public $sstGroupName;

	public function rules()
	{
		return array(
			array('dn, sstDisplayName, sstGroupName', 'required', 'on' => 'update'),
			array('sstDisplayName, sstGroupName', 'required', 'on' => 'create'),
			array('dn', 'safe', 'on' => 'create'),
			array('sstGroupName', 'uniqueGroupName', 'filter'=>'(sstGroupName={sstGroupName})'),
		);
	}

	public function uniqueGroupName($attribute, $params) {
		$check = true;
		if (!is_null($this->dn) && '' != $this->dn) {
			$group = CLdapRecord::model('LdapGroup')->findByDn($this->dn);
			if ($group->sstGroupName == $this->$attribute) {
				$check = false;
			}
		}
		if ($check) {
			$server = CLdapServer::getInstance();
			$criteria = array();
			$count = 0;
			$criteria['branchDn'] = 'ou=groups';
			$criteria['filter'] = str_replace('{' . $attribute . '}', $this->$attribute, $params['filter']);
			$result = $server->findAll(null, $criteria);
			$count = $result['count'];
			if(0 < $count) {
				$this->addError($attribute, Yii::t('user', 'Groupname already in use!'));
			}
		}
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'sstDisplayName' => Yii::t('group', 'Name'),
			'sstGroupName' => Yii::t('group', 'ext. Name'),
		);
	}

}