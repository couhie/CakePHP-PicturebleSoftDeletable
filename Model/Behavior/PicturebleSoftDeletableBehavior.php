<?php
/**
 * pictureble_soft_deletable.php
 * @author kohei hieda
 *
 * ※SoftDeletableBehaviorよりも前にBehaviorへの設定が必須※
 *
 */
class PicturebleSoftDeletableBehavior extends ModelBehavior {

	/**
	 * setup
	 * @param $model
	 * @param $config
	 */
	function setup(&$model, $config) {
		$default = array(
			'modelName'=>'PictureOriginal');

		$this->settings[$model->alias] = Set::merge($default, $config);
	}

	/**
	 * beforeDelete
	 * @param $model
	 * @param $cascade
	 */
	function beforeDelete(&$model, $cascade = true) {
		if (empty($model->actsAs['Pictureble.Pictbind'])) {
			return true;
		}
		if (empty($model->actsAs['SoftDeletable.SoftDeletable'])) {
			return true;
		}
		if (!$model->isEnableSoftDeletable('delete')) {
			return true;
		}
		if ($model->alias == $this->settings[$model->alias]['modelName']) {
			return true;
		}

		$pictPrimaryKey = $model->pictPrimaryKey();
		$pictFields = $model->pictFields();

		$conditions = array(
			$model->primaryKey=>$model->id);
		$params = array(
			'fields'=>$pictFields,
			'conditions'=>$conditions,
			'recursive'=>-1);
		$data = $model->find('first', $params);

		foreach ($data[$model->alias] as $fieldName=>$value) {
			if (!in_array($fieldName, $pictFields)) {
				continue;
			}
			if (!empty($data[$model->alias][$fieldName.'_present_file'])) {
				$data[$model->alias][$fieldName.'_delete'] = true;
			}
		}

		$model->data = Set::merge($model->data, $data);

		return true;
	}

}