<?php

namespace suplascripts\models\supla;

use Supla\ApiClient\SuplaApiClient;
use suplascripts\models\User;

class SuplaApiReal extends SuplaApi {

    /** @var SuplaApiClient */
    private $client;

    private $devices;

    protected function __construct(User $user) {
        $apiCredentials = $user->getApiCredentials();
        $this->client = new SuplaApiClient($apiCredentials, false, false, false);
    }

    public function getDevices(): array {
        if (!$this->devices) {
            $response = $this->client->ioDevices();
            $this->handleError($response);
            $this->devices = $response->iodevices;
        }
        return $this->devices;
    }

    public function getChannelWithState(int $channelId) {
        foreach ($this->getDevices() as $device) {
            foreach ($device->channels as $channel) {
                if ($channel->id == $channelId) {
                    $state = $this->getChannelState($channelId);
                    return (object)array_merge((array)$channel, (array)$state);
                }
            }
        }
        throw new SuplaApiException($this->client, 'Could not get status for channel #' . $channelId);
    }

    public function getChannelState(int $channelId) {
        $state = $this->client->channel($channelId);
        $this->handleError($state);
        return $state;
    }

    public function turnOn(int $channelId) {
        $result = $this->client->channelTurnOn($channelId);
        if ($result === false) {
            $result = $this->toggleUnpredictable($channelId);
        }
        return $result !== false;
    }

    public function turnOff(int $channelId) {
        $result = $this->client->channelTurnOff($channelId);
        if ($result === false) {
            $result = $this->toggleUnpredictable($channelId);
        }
        return $result !== false;
    }

    public function toggle(int $channelId) {
        $state = $this->getChannelState($channelId);
        $this->handleError($state);
        if (isset($state->on)) {
            return $state->on ? $this->turnOff($channelId) : $this->turnOn($channelId);
        } else {
            return $this->toggleUnpredictable($channelId);
        }
    }

    private function toggleUnpredictable(int $channelId) {
        $result = $this->client->channelOpenClose($channelId);
        if ($result === false) {
            $result = $this->client->channelOpen($channelId);
        }
        return $result !== false;
    }

    public function setRgb(int $channelId, string $color, int $colorBrightness = 100, int $brightness = 100) {
        if ($color == 'random') {
            $color = rand(1, 0xFFFFFF);
        } else {
            $color = hexdec($color);
        }
        $result = $this->client->channelSetRGBW($channelId, $color, $colorBrightness, $brightness);
        if ($result === false) {
            $result = $this->client->channelSetRGB($channelId, $color, $colorBrightness);
        }
        return $result !== false;
    }

    public function getSensorLogs(int $channelId, $fromTime = '-1day'): array {
        $fromTime = strtotime($fromTime);
        $timeDiff = abs(time() - $fromTime);
        $withHumidity = false;
        $totalLogCount = $this->client->temperatureLogItemCount($channelId);
        if (!$totalLogCount) {
            $withHumidity = true;
            $totalLogCount = $this->client->temperatureAndHumidityLogItemCount($channelId);
        }
        $this->handleError($totalLogCount);
        // SUPLA you can fetch 4k logs at max and one log is for 10 minutes
        $desiredLogCount = min($totalLogCount->record_limit_per_request ?? 5000, ceil($timeDiff / 600));
        $totalLogCount = intval($totalLogCount->count);
        // logs are ordered ascending (!) so we need to get desired count from the end
        $offset = max(0, $totalLogCount - $desiredLogCount);
        $result = $withHumidity
            ? $this->client->temperatureAndHumidityLogGetItems($channelId, $offset, $desiredLogCount)
            : $this->client->temperatureLogGetItems($channelId, $offset, $desiredLogCount);
        $this->handleError($result);
        $result = array_values(array_filter($result->log, function ($entry) use ($fromTime) {
            return $entry->date_timestamp >= $fromTime;
        }));
        return $result;
    }

    public function shut(int $channelId, int $percent = 100) {
        return $this->client->channelShut($channelId, $percent);
    }

    public function reveal(int $channelId, int $percent = 100) {
        return $this->client->channelReveal($channelId, $percent);
    }

    private function handleError($response) {
        if (!$response) {
            throw new SuplaApiException($this->client);
        }
    }

    public function getClient(): SuplaApiClient {
        return $this->client;
    }
}
