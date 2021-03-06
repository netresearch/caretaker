<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2009-2011 by n@work GmbH and networkteam GmbH
 *
 * All rights reserved
 *
 * This script is part of the Caretaker project. The Caretaker project
 * is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * This is a file of the caretaker project.
 * http://forge.typo3.org/projects/show/extension-caretaker
 *
 * Project sponsored by:
 * n@work GmbH - http://www.work.de
 * networkteam GmbH - http://www.networkteam.com/
 *
 * $Id$
 */

/**
 *
 */
class tx_caretaker_NotificationBaseExitPoint implements tx_caretaker_NotificationExitPointInterface {

	/**
	 * @var array 
	 */
	protected $config = array();

	/**
	 * @param array $notification
	 * @param array $config
	 * @return void
	 */
	public function addNotification($notification, $config) {
	}

	/**
	 * @param array $config
	 * @return void
	 */
	public function init(array $config) {
		$this->config = $this->flattenFlexformConfig($config);
	}

	/**
	 * @return void
	 */
	public function execute() {
	}

	/**
	 * @param array $flexformConfig
	 * @return
	 */
	protected function flattenFlexformConfig($flexformConfig) {
		foreach ($flexformConfig['data']['sDEF']['lDEF'] as $key => $value) {
			$config[$key] = $value['vDEF'];
		}
		return $config;
	}

	/**
	 * @param array $overrideConfig
	 * @return array
	 */
	protected function getConfig($overrideConfig) {
		$config = $this->config;
		if (is_array($overrideConfig)) {
			$config = t3lib_div::array_merge_recursive_overrule($this->config, $overrideConfig);
		}
		return $config;
	}

	/**
	 * @param array $notification
	 * @return string
	 */
	protected function getMessageForNotification($notification) {
		$ancestorResult = $notification['node']->getPreviousDifferingResult($notification['result']);
		$ancestorResultPrev = $notification['node']->getPreviousDifferingResult($ancestorResult);

		$durationStateBefore = ($notification['result'] && $ancestorResult->getTimestamp() > 0 && $ancestorResultPrev->getTimestamp() > 0 ?
				$ancestorResult->getTimestamp() - $ancestorResultPrev->getTimestamp() :
				0);
		$durationState = ($notification['result'] && $notification['result']->getTimestamp() > 0 && $ancestorResult->getTimestamp() > 0 ?
				$notification['result']->getTimestamp() - $ancestorResult->getTimestamp() :
				0);

			// TODO l10n
			// TODO template

		$messages = array();
		$messages[] = ($notification['result'] ? 'Date/Time: ' . date('Y-m-d H:i:s', $notification['result']->getTimestamp()) : '');
		$messages[] = 'Instance: ' .
			($notification['node'] instanceof tx_caretaker_AbstractNode && $notification['node']->getInstance() ?
				'"' . $notification['node']->getInstance()->getTitle() . '"' :
				'-'
			) .
			($notification['node'] instanceof tx_caretaker_AbstractNode ?
				' [' . $notification['node']->getCaretakerNodeId() . ']' :
				'-'
			);
		$messages[] = 'Test: ' . $notification['node']->getTitle();
		$messages[] = ($notification['result'] ? 'State is now: ' . $notification['result']->getLocallizedStateInfo() .
				($durationState > 0 ? ' (since ' . $this->humanReadableTime($durationState) . ')' : '' ) :
				'');
		$messages[] = ($ancestorResult ?
			'State before: ' . $ancestorResult->getLocallizedStateInfo() .
			($durationStateBefore > 0 ? ' (was ' . $this->humanReadableTime($durationStateBefore) . ')' : '' ) :
			'');
		$messages[] = ($notification['result'] ? 'Info: ' . chr(10) . $notification['result']->getLocallizedInfotext() : '');
		$messages[] = '';
		$messages[] = '----------------------------------------------------';

		return implode(chr(10), $messages) . chr(10);
	}

	/**
	 * @param int $time
	 * @return string
	 */
	protected function humanReadableTime($time) {
		$periods = array("sec", "min", "hour", "day", "week", "month", "year", "decade");
		$lengths = array("60", "60", "24", "7", "4.35", "12", "10");
        for ($j = 0; $time >= $lengths[$j]; $j++) {
	        if ($lengths[$j] == 0) break;
		$time /= $lengths[$j];
        }
        $time = round($time);
        if ($time != 1) $periods[$j] .= "s";
        return $time . ' ' . $periods[$j];
	}
}

?>
