<?php
/**
 * Fundraisers end point model.
 *
 * This model emulates the events api by exposing all events into the app.
 *
 * @package: Framework
 * @category: Model
 * @author: Kris Pomphrey <kris@krispomphrey.co.uk>
 */
class EventsModel extends Model{

  /**
   * Implements init();
   *
   * Initialise the Events Model.
   *
   * @return void
   */
  public function init(){
    // Pull in some dummy records into the data.
    $dummy_content = MODEL_ROOT . 'data/Events.json';
    $this->read_file($dummy_content);
  }
}
