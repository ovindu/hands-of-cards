<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

trait ScoreTrait
{
    private $inputKeys = array(); // temporary variable to store user entered cards
    private $escapeKeys = array("BACKSPACE" => "BACKSPACE", "DELETE" => "DELETE"); // escape keys and delete items if user enter these keys

    /**
     * return a data set by grouping the name of the user and corresponding number of games and wins the user has in the system
     * @param $model
     * @return mixed
     *
     */
    public function getScores($model)
    {
        return $model->select("name", DB::raw("COUNT(name) as games"), DB::raw("SUM(status) as wins"))->groupBy('name')->get();
    }

    /**
     *
     * validate function as user enter cards
     * @param Request $request
     * @param $cards
     * @return array|mixed
     *
     */
    public function validateCards(Request $request, $cards)
    {
        $inputValue = strtoupper($request->input('input_value'));
        $inputKey = strtoupper($request->input('key'));

        // set the session temporary variable to store user cards
        if (Session::has('arrayInputs')) {
            $this->inputKeys = Session::get('arrayInputs');
        }

        // if the input box value is null we clear the session variable
        if (!$inputValue) {
            Session::put('arrayInputs', []);
            $this->inputKeys = [];
        }

        // this will be true as long as user doesn't enter any escape key
        if (!array_key_exists($inputKey, $this->escapeKeys)) {
            // condition to identify if user tries to enter 10
            if ($inputKey === '1' || $inputKey === '0') {
                // set 10 as if the user enter cards 1 and 0 respectively
                if ($inputKey === '0' && (strrpos($inputValue, '1') === strrpos($inputValue, '0') - 1)) {
                    $response = $this->validateKey($cards, "10");
                } else {
                    // else set 1
                    $response = $this->validateKey($cards, "1");
                }
            } else {
                // validate any other card entered by the user and set the response coming from the function
                $response = $this->validateKey($cards, $inputKey);
            }
        } else {
            // get session variable data and remove last element based on the user action
            $this->inputKeys = Session::get('arrayInputs');
            array_pop($this->inputKeys);
            Session::put('arrayInputs', $this->inputKeys);

            // set the response
            $response = ['error' => false, 'message' => 'Card provided', 'data' => count($this->inputKeys) ? implode(" ", $this->inputKeys) : []];
        }

        // call response function
        return $this->jsonResponse($response);
    }

    /**
     *
     * generate system hand and compare against user hand
     * save final values and send the response to the frontend
     * @param Request $request
     * @param $cards
     * @param $model
     * @return JsonResponse
     *
     */
    public function generateCards(Request $request, $cards, $model)
    {
        $this->inputKeys = Session::get('arrayInputs'); // get user entered cards so far
        $cards_local = $cards;
        shuffle($cards_local); // shuffling the system cards to generate random values every time
        $cards_local = array_slice($cards_local, 0, count($this->inputKeys)); // get system cards matching to the user entered cards length

        // init values for the database save
        $data['name'] = $request->input('name');
        $data['user_score'] = 0;
        $data['system_score'] = 0;

        $systemHand = [];
        // compare user hand and system hand and set values
        foreach ($this->inputKeys as $key => $inputKey) {
            $user_card_value = $this->getCardValue($cards, $inputKey);
            $system_card_value = $cards_local[$key]['value'];
            $systemHand[$key] = $cards_local[$key]['card'];

            if ($system_card_value > $user_card_value) {
                $data['system_score'] += 1;
            } else {
                $data['user_score'] += 1;
            }
        }

        // database value if user win or loos
        $data['status'] = $data['user_score'] > $data['system_score'];
        // saving the result
        $this->saveScore($data, $model);

        // generate two hands to show in the frontend
        $hands = ['player' => implode(" ", $this->inputKeys), 'system' => implode(" ", $systemHand)];

        // set the response
        $response = ['player' => $data['user_score'], 'system' => $data['system_score'], 'status' => $data['status'], 'hands' => $hands];

        // clear the session variable to prevent unexpected user experience
        Session::put('arrayInputs', []);
        $this->inputKeys = [];

        // send the json response to frontend
        return $this->jsonResponse(['error' => false, 'message' => 'Card provided', 'data' => $response]);
    }

    /**
     *
     * get each card value for user entered card
     * @param $cards
     * @param $key
     * @return mixed
     *
     */
    public function getCardValue($cards, $key)
    {
        $cards = array_filter($cards, function ($card) use ($key) {
            return $card['card'] === $key;
        }, 1);
        sort($cards);
        return $cards[0]['value'];
    }

    /**
     *
     * save score in database
     * @param $data
     * @param $model
     * @return mixed
     *
     */
    public function saveScore($data, $model)
    {
        return $model::create($data);
    }

    /**
     *
     * validate function to identify user entered keys are exist in the system
     * @param $cards
     * @param $key
     * @return arr
     * ay
     */
    public function validateKey($cards, $key)
    {
        $keyExists = count(array_filter($cards, function ($card) use ($key) {
            return $card['card'] === $key;
        }, 1));

        if ($keyExists) {
            $response = ['error' => false, 'message' => 'Card provided', 'data' => $this->setWhiteSpaces($key)];
        } else {
            $response = ['error' => true, 'message' => 'Invalid Card provided', 'data' => $key];
        }

        return $response;
    }

    /**
     *
     * create the session variable to store user entered cards and set white space string to show in frontend
     * @param $key
     * @return mixed
     *
     */
    public function setWhiteSpaces($key)
    {
        array_push($this->inputKeys, strtoupper($key));
        Session::put('arrayInputs', $this->inputKeys);
        return implode(" ", $this->inputKeys);
    }

    /**
     *
     * global json response which can be used on any class available in the system. I would use separate Trait, but this should enough for this app
     * @param $response
     * @return JsonResponse
     *
     */
    public function jsonResponse($response)
    {
        return response()->json($response, 200);
    }
}
