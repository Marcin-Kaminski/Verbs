<?php

namespace Tests\Unit;

use App\Orchid\Screens\VerbsLearningScreenx;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class VerbsLearningScreenTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->verbsLearningScreen = new VerbsLearningScreenx();
        $this->verbsArray = [
            [
                'verb_in_polish' => 'Bić',
                'verb_in_infinitive' => 'Beat',
                'verb_in_past_simple' => 'Beat',
                'verb_in_past_participle' => 'Beaten',
                'how_many_times' => 0
            ]
        ];
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Cache::flush();
    }
    public function testShouldPassIfClearningCacheWorks(): void
    {
        $fakeCache = 'string';
        Cache::add('test', $fakeCache, now()->addMinute());
        $this->verbsLearningScreen->clearCache();
        $retrievedCache = Cache::get('test');
        $this->assertEmpty($retrievedCache);
    }
    public function testShouldPassIfDrawnAndSavedToCacheCorrectly()
    {
        $this->verbsLearningScreen->drawAndSaveVerbToCache($this->verbsArray);
        $result = Cache::get('random_verb');
        $expectedResult = [
            "polish" => "Bić",
            "infinitive" => "Beat",
            "past_simple" => "Beat",
            "past_participle" => "Beaten",
            "how_many_times" => 0
            ];
        $this->assertEquals($expectedResult, $result);
    }
    public function testShouldPassIfCorrectlyIncrementedVerbHowManyTimesProperty()
    {
        $verbToIncrement = 'Bić';
        $this->verbsArray[0]['verb_in_polish'] = 'bić';
        Cache::put('verbs_left_to_learn', $this->verbsArray, now()->addMinute());
        $result = $this->verbsLearningScreen->incrementVerbHowManyTimes($verbToIncrement);
        $this->assertEquals(1, $result);
    }
    public function testShouldPassIfReturnedErrorInIncrementVerbHowManyTimesFunction()
    {
        $verbToIncrement = 'Bić';
        $verbsArray = [];
        Cache::put('verbs_left_to_learn', $verbsArray, now()->addMinute());
        $result = $this->verbsLearningScreen->incrementVerbHowManyTimes($verbToIncrement);
        $this->assertEquals('Błąd', $result);
    }
    public function testShouldPassIfCorrectlyUnsetVerbFromArray()
    {
        $verbToRemove = 'Bić';
        Cache::put('verbs_left_to_learn', $this->verbsArray, now()->addMinute());
        $result = $this->verbsLearningScreen->unsetVerbFromArray($verbToRemove);
        $this->assertNull($result);
    }
}
