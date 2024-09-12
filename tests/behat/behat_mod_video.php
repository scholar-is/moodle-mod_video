<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Behat tests.
 *
 * @package    mod_video
 * @copyright  2024 Scholaris <https://scholar.is>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use Behat\Behat\Context\SnippetAcceptingContext;

/**
 * Behat step code.
 */
class behat_mod_video extends behat_base implements SnippetAcceptingContext {

    /**
     * I wait until the Plyr play button appears.
     * @Then /^I wait until the Plyr play button appears$/
     */
    public function i_wait_until_the_plyr_play_button_appears(): void {
        $this->wait_or_error(5000, "document.querySelector('button[data-plyr=\"play\"]') !== null");
    }

    /**
     * I click on the play button.
     * @Given /^I click on the play button$/
     * @throws Exception
     */
    public function i_click_on_the_play_button(): void {
        $cssselector = '.plyr__control[data-plyr="play"]';
        $element = $this->getSession()->getPage()->find('css', $cssselector);

        if (null === $element) {
            throw new \InvalidArgumentException(sprintf('Could not find the play button with CSS selector: "%s"', $cssselector));
        }

        $element->click();
    }

    /**
     * I wait until the video player is ready.
     * @Then /^I wait until the video player is ready$/
     */
    public function i_wait_until_the_video_player_is_ready(): void {
        $this->wait_or_error(5000, "document.querySelector('[id^=\"video-\"]').classList.contains('ready')");
    }

    /**
     * The video should start playing.
     * @Then /^the video should start playing$/
     */
    public function the_video_should_start_playing(): void {
        $cssselector = '[id^="video-"] .plyr--playing';
        $this->getSession()->wait(
            5000,  // Wait up to 5000 milliseconds.
            "document.querySelector('{$cssselector}') !== null"
        );
    }

    /**
     * Wait for a condition to be true. If the condition isn't true within the duration, throw an error.
     * @param int $time
     * @param string $condition
     * @return void
     * @throws Exception
     */
    public function wait_or_error(int $time, string $condition): void {
        // Wait up to 5000 milliseconds for the custom play button to appear.
        $result = $this->getSession()->wait(
            $time,
            $condition
        );

        if (!$result) {
            throw new \Exception("Condition ($condition) did not resolve within $time ms");
        }
    }
}
