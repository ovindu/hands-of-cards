<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScoreSaveRequest;
use App\Http\Resources\ScoreResource;
use App\Score;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

/**
 * @property Score model
 */
class ScoreController extends Controller
{
    /**
     * ScoreController constructor.
     * @param Score $model
     */
    public function __construct(Score $model)
    {
        $this->model = $model;
    }

    /**
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        // return a data set by grouping the name of the user and corresponding number of games and wins the user has in the system
        return ScoreResource::collection($this->model->select("name", DB::raw("COUNT(name) as games"), DB::raw("SUM(status) as wins"))->groupBy('name')->get());
    }

    /**
     * @param ScoreSaveRequest $request
     * @return ScoreResource
     */
    public function save(ScoreSaveRequest $request)
    {
        // save new data set provided from the frontend
        return new ScoreResource($this->model::create($request->all()));
    }
}
