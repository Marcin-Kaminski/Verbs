<?php

namespace Tests\Unit;

use App\Orchid\Screens\English\VerbsLearningScreenx;
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
                'how_many_times' => 0,
                'errors' => 0
            ]
        ];
    }

    public function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }

    /**
     * Test sprawdza, czy cache jest czyszczone poprawnie
     *
     * @return void
     */
    public function testShouldPassIfClearningCacheWorks(): void
    {
        $fakeCache = 'string';
        Cache::add('test', $fakeCache, now()->addMinute());
        $this->verbsLearningScreen->clearCache();
        $retrievedCache = Cache::get('test');
        $this->assertEmpty($retrievedCache);
    }

    /**
     * Test sprawdza, czy funkcja poprawnie formatuje i zapisuje czasownik do cache
     *
     * @return void
     */
    public function testShouldPassIfDrawnAndSavedToCacheCorrectly()
    {
        $this->verbsLearningScreen->drawAndSaveVerbToCache($this->verbsArray);
        $result = Cache::get('random_verb');
        $expectedResult = [
            "verb_in_polish" => "Bić",
            "verb_in_infinitive" => "Beat",
            "verb_in_past_simple" => "Beat",
            "verb_in_past_participle" => "Beaten",
            "how_many_times" => 0,
            "errors" => 0
            ];
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Test sprawdza, czy ilość wypełnień poprawnie się inkrementuje
     *
     * @return void
     */
    public function testShouldPassIfCorrectlyIncrementedVerbHowManyTimesProperty()
    {
        $verbToIncrement = 'Bić';
        $this->verbsArray[0]['verb_in_polish'] = 'bić';
        Cache::put('verbs_left_to_learn', $this->verbsArray, now()->addMinute());
        $result = $this->verbsLearningScreen->incrementVerbHowManyTimes($verbToIncrement);
        $this->assertEquals(1, $result);
    }

    /**
     * Test sprawdza, czy obsługa błędów działa
     *
     * @return void
     */
    public function testShouldPassIfReturnedErrorInIncrementVerbHowManyTimesFunction()
    {
        $verbToIncrement = 'Bić';
        $verbsArray = [];
        Cache::put('verbs_left_to_learn', $verbsArray, now()->addMinute());
        $result = $this->verbsLearningScreen->incrementVerbHowManyTimes($verbToIncrement);
        $this->assertEquals('Błąd', $result);
    }

    /**
     * Test sprawdza, czy czasownik poprawnie usuwa się z tablicy
     *
     * @return void
     */
    public function testShouldPassIfCorrectlyUnsetVerbFromArray()
    {
        $verbToRemove = 'Bić';
        Cache::put('verbs_left_to_learn', $this->verbsArray, now()->addMinute());
        $result = $this->verbsLearningScreen->unsetVerbFromArray($verbToRemove);
        $this->assertNull($result);
    }

    /**
     * Test sprawdza, czy liczba błędów poprawnie się inkrementuje
     *
     * @return void
     */
    public function testShouldPassIfCorrectlyIncrementedErrorsCount()
    {
        $verbToIncrement = 'Bić';
        $this->verbsArray[0]['verb_in_polish'] = 'bić';
        Cache::put('verbs_left_to_learn', $this->verbsArray, now()->addMinute());
        $this->verbsLearningScreen->incrementVerbErrorsCount($verbToIncrement);
        $result = Cache::get('random_verb');
        $this->assertEquals(1, $result['errors']);
    }
}
