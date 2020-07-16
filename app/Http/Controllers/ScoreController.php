<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScoreSaveRequest;
use App\Http\Requests\ScoreValidateRequest;
use App\Http\Resources\ScoreResource;
use App\Http\Traits\ScoreTrait;
use App\Score;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @property Score model
 */
class ScoreController extends Controller
{
    use ScoreTrait;

    /**
     * @var array
     */
    private $cards;

    /**
     * ScoreController constructor.
     * @param Score $model
     */
    public function __construct(Score $model)
    {
        $this->model = $model; // model init
        $this->cards = array(
            1 => array(
                'card' => '2',
                'value' => 2
            ),
            2 => array(
                'card' => '3',
                'value' => 3
            ),
            3 => array(
                'card' => '4',
                'value' => 4
            ),
            4 => array(
                'card' => '5',
                'value' => 5
            ),
            5 => array(
                'card' => '6',
                'value' => 6
            ),
            6 => array(
                'card' => '7',
                'value' => 7
            ),
            7 => array(
                'card' => '8',
                'value' => 8
            ),
            8 => array(
                'card' => '9',
                'value' => 9
            ),
            9 => array(
                'card' => '10',
                'value' => 10
            ),
            10 => array(
                'card' => 'J',
                'value' => 11
            ),
            11 => array(
                'card' => 'Q',
                'value' => 12
            ),
            12 => array(
                'card' => 'K',
                'value' => 12
            ),
            13 => array(
                'card' => 'A',
                'value' => 13
            ),
        ); // default cards init
    }

    /**
     *
     * function call for getting leaderboard scores
     * @return AnonymousResourceCollection
     *
     */
    public function index()
    {
        return ScoreResource::collection($this->getScores($this->model));
    }

    /**
     *
     * function call to validate the cards as user enters and send the json response from the function
     * @param ScoreValidateRequest $request
     * @return mixed
     *
     */
    public function getValidate(ScoreValidateRequest $request)
    {
        return $this->validateCards($request, $this->cards);
    }

    /**
     *
     * Generate the system hash and saving in the database then return the json response
     * @param ScoreSaveRequest $request
     * @return JsonResponse
     *
     */
    public function getAndSaveCards(ScoreSaveRequest $request)
    {
        return $this->generateCards($request, $this->cards, $this->model);
    }
}
