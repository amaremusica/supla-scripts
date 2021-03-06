<?php

namespace suplascripts\models\scene;

use Assert\Assertion;
use suplascripts\models\HasSuplaApi;
use suplascripts\models\supla\SuplaApiException;

class SceneExecutor {

    const OPERATION_DELIMITER = '|';
    const CHANNEL_DELIMITER = ';';
    const ARGUMENT_DELIMITER = ',';

    use HasSuplaApi;

    public function executeCommandFromString($command) {
        list($channelId, $action) = explode(self::CHANNEL_DELIMITER, $command);
        $args = explode(self::ARGUMENT_DELIMITER, $action);
        $action = array_shift($args);
        Assertion::inArray($action, ['turnOn', 'turnOff', 'toggle', 'getChannelState', 'setRgb', 'shut', 'reveal']);
        array_unshift($args, $channelId);
        $this->getApi()->clearCache($channelId);
        return call_user_func_array([$this->getApi(), $action], $args);
    }

    private function executeCommandsFromString($commands) {
        $commands = explode(self::OPERATION_DELIMITER, $commands);
        $results = [];
        foreach ($commands as $command) {
            try {
                $results[] = $this->executeCommandFromString($command);
            } catch (SuplaApiException $e) {
                $results[] = false;
            }
        }
        return $results;
    }

    public function executeWithFeedback(Scene $scene): string {
        $scene->lastUsed = new \DateTime();
        $scene->save();
        if ($scene->actions) {
            $this->executeCommandsFromString($scene->actions);
            $scene->log('Wykonanie');
        }
        if ($scene->feedback) {
            $feedback = (new FeedbackInterpolator())->interpolate($scene->feedback);
            $scene->log('Odpowiedź: ' . $feedback);
            return $feedback;
        } else {
            return '';
        }
    }
}
