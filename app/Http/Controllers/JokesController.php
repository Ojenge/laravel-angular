<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Joke;

use App\User;

class JokesController extends Controller
{

  public function __construct(){
    $this->middleware('jwt.auth');

  }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        /*checking to see, if the limit is present as query string,
         if yes use that value otherwise the no. of elements are default to 5
         */
        $limit = $request->input('limit')?$request->input('limit'):5;

        //implementing Search
        $search_term = $request->input('search');
        if ($search_term)
        {
            $jokes = Joke::orderBy('id', 'DESC')->where('body', 'LIKE', "%$search_term%")->with(
            array('User'=>function($query){
                $query->select('id','name');
            })
            )->select('id', 'body', 'user_id')->paginate($limit);

            $jokes->appends(array(
                'search' => $search_term,
                'limit' => $limit
            ));
        }
        else
        {
            $jokes = Joke::orderBy('id', 'DESC')->with(
            array('User'=>function($query){
                $query->select('id','name');
            })
            )->select('id', 'body', 'user_id')->paginate($limit);

            $jokes->appends(array(
                'limit' => $limit
            ));
        }
        return response()->json($this->transformCollection($jokes), 200);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      if(! $request->body or ! $request->user_id)
      {
          return response()->json(['error' => ['message' => 'Please Provide Both body and user_id']], 422);
      }

      //creating a new joke
      $joke = Joke::create($request->all());

      //return success message once a joke has been made
      return response()->json([
              'message' => 'Joke Created Succesfully',
              'data' => $this->transform($joke)
            ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //first get the joke with the $id
        //then get the user who made the joke

        $joke = Joke::with(array('User'=>function($query){$query->select('id','name');}))->find($id);

        //if the joke does not exist return error
        if(!$joke){
          return response()->json(['error'=>['message'=>'Joke does not exist']], 404);

        }
        // get previous joke id
        $previous = Joke::where('id', '<', $joke->id)->max('id');

       // get next joke id
       $next = Joke::where('id', '>', $joke->id)->min('id');

       return response()->json([
           'previous_joke_id'=> $previous,
           'next_joke_id'=> $next,
           'data' => $this->transform($joke)
       ], 200);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
      if(! $request->body or ! $request->user_id){
          return response()->json([
              'error' => [
                  'message' => 'Please Provide Both body and user_id'
              ]
          ], 422);
      }

      //find the specific joke
      $joke = Joke::find($id);
      //update the specifics of the joke
      $joke->body = $request->body;
      $joke->user_id = $request->user_id;
      //save the changes
      $joke->save();

      return response()->json([
              'message' => 'Joke Updated Succesfully'
      ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //delete the jokes
        Joke::destroy($id);
    }
    private function transformCollection($jokes)
    {
        $jokesArray = $jokes->toArray();
        return [
           'total' => $jokesArray['total'],
           'per_page' => intval($jokesArray['per_page']),
           'current_page' => $jokesArray['current_page'],
           'last_page' => $jokesArray['last_page'],
           'next_page_url' => $jokesArray['next_page_url'],
           'prev_page_url' => $jokesArray['prev_page_url'],
           'from' => $jokesArray['from'],
           'to' =>$jokesArray['to'],
           'data' => array_map([$this, 'transform'], $jokesArray['data'])
       ];
    }

    private function transform($joke)
    {
        return [
          'joke_id' => $joke['id'],
          'joke' => $joke['body'],
          'submitted_by' => $joke['user']['name']
          ];
    }
}
