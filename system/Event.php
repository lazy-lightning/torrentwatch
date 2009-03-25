<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Process queuing/execution class. Allows an unlimited number of callbacks
 * to be added to 'events'. Events can be run multiple times, and can also
 * process event-specific data. By default, Kohana has several system events.
 *
 * $Id: Event.php 3917 2009-01-21 03:06:22Z zombor $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007 Kohana Team
 * @license    http://kohanaphp.com/license.html
 * @link       http://docs.kohanaphp.com/general/events
 */
final class Event {

	// Event callbacks
	private static $events = array();

	// Cache of events that have been run
	private static $has_run = array();

	// Data that can be processed during events
	public static $data = '';

  // Debug stack of currently running events
  public static $running = array();

	/**
	 * Add a callback to an event queue.
	 *
	 * @param   string   event name
	 * @param   array    http://php.net/callback
	 * @return  boolean
	 */
	public static function add($name, $callback)
	{
		if ( ! isset(self::$events[$name]))
		{
			// Create an empty event if it is not yet defined
			self::$events[$name] = array();
		}
		elseif (in_array($callback, self::$events[$name], TRUE))
		{
			// The event already exists
			return FALSE;
		}

		// Add the event
		self::$events[$name][] = $callback;

		return TRUE;
	}

  public static function add_first($name, $callback) {
    if(!isset(self::$events[$name])) {
      self::$events[$name] = array();
    } elseif(in_array($callback, self::$events[$name], TRUE)) {
      return False;
    }

    array_unshift(self::$events[$name], $callback);

    return True;
  }

	/**
	 * Get all callbacks for an event.
	 *
	 * @param   string  event name
	 * @return  array
	 */
	public static function get($name)
	{
		return empty(self::$events[$name]) ? array() : self::$events[$name];
	}

	/**
	 * Clear some or all callbacks from an event.
	 *
	 * @param   string  event name
	 * @param   array   specific callback to remove, FALSE for all callbacks
	 * @return  void
	 */
	public static function clear($name, $callback = FALSE)
	{
		if ($callback === FALSE)
		{
			self::$events[$name] = array();
		}
		elseif (isset(self::$events[$name]))
		{
			// Loop through each of the event callbacks and compare it to the
			// callback requested for removal. The callback is removed if it
			// matches.
			foreach (self::$events[$name] as $i => $event_callback)
			{
				if ($callback === $event_callback)
				{
					unset(self::$events[$name][$i]);
				}
			}
		}
	}

	/**
	 * Execute all of the callbacks attached to an event.
	 *
	 * @param   string   event name
	 * @param   array    data can be processed as Event::$data by the callbacks
	 * @return  void
	 */
	public static function run($name, & $data = NULL)
	{
		if ( ! empty(self::$events[$name]))
		{
      // Add to running event debug stack
      self::$running[] = $name;
      $id = end(array_keys(self::$running));

      // Preserve data for nested events
      $old_data =& self::$data;

			// So callbacks can access Event::$data
			self::$data =& $data;
			$callbacks  =  self::get($name);

      SimpleMvc::Log("Event: $name - ".count($callbacks)." registered callbacks");

			foreach ($callbacks as $callback)
			{
				call_user_func($callback);
			}

      // Restore the data for nested events
			self::$data =& $old_data;

			// The event has been run!
			self::$has_run[$name] = $name;

      // Remove from running event stack
      unset(self::$running[$id]);
		}
	}

	/**
	 * Check if a given event has been run.
	 *
	 * @param   string   event name
	 * @return  boolean
	 */
	public static function has_run($name)
	{
		return isset(self::$has_run[$name]);
	}

} // End Event
