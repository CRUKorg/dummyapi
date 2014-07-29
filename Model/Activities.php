<?php
/**
 * Fundraisers end point model.
 * Model holds the validation rules for fundraisers end point.
 *
 * @package: Framework
 * @category: Model
 * @author: Kris Pomphrey <kris@krispomphrey.co.uk>
 */
class ActivitiesModel extends Model{

  /**
   * Implements init();
   *
   * Initialise the Fundraisers Model.
   */
  public function init(){
    // Pull in some dummy records into the data.
    $dummy_content = MODEL_ROOT . 'ActivityCodes.json';
    $this->read_file($dummy_content);
  }
}
