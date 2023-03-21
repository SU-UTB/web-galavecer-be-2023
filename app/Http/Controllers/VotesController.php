<?php

namespace App\Http\Controllers;

use App\Models\Nominee;
use App\Models\Vote;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="SU Galavecer - Api Documentation",
 *     description="Api Documentation for UTB Gala Ball",
 *     @OA\Contact(
 *         name="Sedlar David",
 *         email="sedlar@sutb.cz"
 *     ),
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 * ),
 * @OA\Server(
 *     url="/api/v1",
 * ),
 */
class VotesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    /**
     * @OA\Get(
     *    path="/api/votes",
     *    operationId="index",
     *    tags={"Votes"},
     *    summary="Get data for votes index page",
     *    description="Gets list of nominees to render",
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=404, description="Not Found")
     *  )
     */
    public static function index()
    {
        $nominees = Nominee::all();
        return response()->json(
            array(
                'nominees' => $nominees->toArray()
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    /**
     * @OA\Post(
     *   tags={"Votes"},
     *   path="/api/votes",
     *   summary="Creates a vote",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="consent",
     *                     type="integer"
     *                 example={"id": 11, "email" : "tomes@utb.cz", "consent" : 1}
     *             )
     *         )
     *     ),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'id' => ['required'],
            'email' => ['required'],
            'consent' => ['required']
        ]);

        if ((int) $request->input('consent') === 0) {
            return response()->json([
                'error' => 'Consent must be accepted!'
            ], 400);
        }

        $emailValidation = $this->validateEmailExistsInDb($request->input('email'));

        if ($emailValidation === true) {
            return response()->json([
                'error' => 'This email address has already been used!'
            ], 400);
        }

        $vote = Vote::create([
            'nominee_id' => $request->input('id'),
            'voter_email' => $request->input('email'),
            'consent' => (int) $request->input('consent')
        ]);

        $data = ['vote' => $vote];

        return response()->json($data, 200);
    }

    public function NomineesStore(Request $request)
    {
        $request->validate([
            'id' => ['required'],
            'email' => ['required'],
            'consent' => ['required']
        ]);

        $vote = Nominee::create([
            'first_name' => $request->input('nominee_first_name'),
            'last_name' => $request->input('nominee_last_name'),
            'email' => $request->input('nominee_email'),
            'faculty_id' => $request->input('faculty_id'),
            'achievements' => $request->input('achievements')
        ]);

        $data = ['vote' => $vote];

        return response()->json($data, 200);
    }

    function validateEmailExistsInDb($email): bool
    {
        $votes = Vote::all();

        if ($votes->contains('voter_email', $email))
            return true;
        else
            return false;
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return Nominee::find($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $nomination = Nominee::find($id);
        $nomination->update($request->all());
        return $nomination;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroyNominee($id)
    {
        return Nominee::destroy($id);
    }

    public function removeNominee(Request $request, $id)
    {
        $this->destroyNominee($id);
        return AdministrationController::nominees();
    }
}