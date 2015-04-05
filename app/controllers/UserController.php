<?php

class UserController extends \BaseController {

	/**
	 * Display a listing of the User Data.
	 *
	 * @return Response
	 */
	public function index()
	{
		//Return the full users collection
		return User::all()->toJson();
	}

	/**
	 * Show the form for creating a new User Data set.
	 *
	 * @param String $username
	 * @param Boolean $isAdmin
	 * @param String $email
	 * @param String $firstName
	 * @param String $lastName
	 * @param String $gender
	 * @param String $birthday   -formatted date string
	 * 
	 * --Instantiated in method--
	 * Boolean $isVerified
	 * String $picture    		-url to stored image
	 * String $about
	 * int $numFriends
	 * String[] $friends  		-users referenced by ID
	 * String[] $friendRequests
	 * String[] $blockedUsers 	-users referenced by ID
	 * Object[] $awards    		-badges and stuff
	 * int $totalAmountWatched
	 * String[] $catalogueItems -catalogue items by ID
	 * String[] $favorites 		-catalogue items by ID
	 * String $city
	 * String $state
	 * String $province
	 * String $country
	 *
	 * @return true if saved successfully
	 */
	public function insertDocument($username, $isAdmin, $email, $firstName, $lastName, $gender, $birthday)
	{
		$user = new User();

		//from input
		$user->username = $username;
		$user->isAdmin = $isAdmin;
		$user->email = $email;
		$user->firstName = $firstName;
		$user->lastName = $lastName;
		$user->gender = $gender;
		$user->birthday = $birthday;

		//presetting some empty/0 values that will be updated
		$user->joinDate = date('m/d/Y h:i:s a');
		$user->isVerified = false;
		$user->picture = '';
		$user->about = "";
		$user->friends = [];
		$user->friendRequests = [];
		$user->blockedUsers = [];
		$user->awards = [];
		$user->totalAmountWatched = 0;
		$user->catalogueItems = [];
		$user->favorites = [];
		$user->city = '';
		$user->state = '';
		$user->province = '';
		$user->country = '';

		return $user->save();
	}

	/**
	 * Clears the collection
	 * 
	 * @return boolean 	whether or not the op was successful
	 */
	public function destroyEverything()
	{
		return DB::collection('users')->delete();
	}

	/**
	 * Destroys the documents with the matching $id
	 *
	 * @param $id 	mongo hash id
	 * 
	 * @return boolean 	whether or not the op was successful
	 */
	public function destroyDocument($id)
	{
		//Will take in an id, and destroy the Document with that id
		$user = User::find($id);
		return $user->delete();
	}


	/**
	 * Return a User Data document found by ID.
	 *
	 * @param  int  $id
	 *
	 * @return Response
	 */
	public function getDocument($id)
	{
		//Will take in an id, and return a document
		$user = User::find($id);
		return $user;
	}


	/**
	 * Returns $numDocs documents that satisfies the query built from $queryArr
	 * If $numDocs > number of results, it just returns all the results
	 * If $numDocs == 0, it also returns all of the results
	 *
	 * @param  int  $numDocs	the number of documents to return from the query
	 * @param  array 	$queryArr	an array of the elements to build the query
	 *
	 * @return array of documents
	 */
	public function getDocumentsWhere($numDocs, $queryArr)
	{
		return parent::getDocumentsWhereTemplate("User", $numDocs, $queryArr);
	}

	/**
	 * Get a single user by
	 * @param 	username 	the username of the document
	 */
	public function getUser($username){
		return User::where('username', '=', "$username")->firstOrFail();
	}

	/**
	 * Delete the user with the given username from the database
	 * @param 	username
	 */
	public function deleteUser($username){
		return User::where('username', '=', "$username")->delete();
	}

	/**
	 * Get a given user's list of catalogue items
	 * @param 	username
	 */
	public function getUserList($username){
		$user = User::where('username', '=', "$username")->firstOrFail();
		$catalogue = $user->catalogueItems;
		$favorites = $user->favorites;

		return array(
			'catalogueItems' => $catalogue,
			'favorites' => $favorites
			);
	}

	/**
	 * Append a document with a new attribute
	 *
	 * @param  String  $id      -id of document to append
	 * @param  String  $newAttr   -name of new attribute
	 * @param  String  $value   -data of new attribute
	 *
	 * @return true if saved successfully
	 */
	public function appendDocument($id,$newAttr,$value)
	{
		$docToAppend = User::find($id);
		$docToAppend->$newAttr = $value;
		return $docToAppend->save();
	}

	/**
	 * Checks if the username's email is verified
	 * @param String $username
	 */
	public function isVerified($username){
		$docs = $this->getDocumentsWhere(1, array('username', '=', "$username"));
		if(count($docs) < 1) return false;
		return $docs->isVerified;
	}

	/**
	 * Adds/updates the given parameters as an entry in the username's catalogueItems array
	 * @param $username
	 * @param $itemId
	 * @param $rating
	 * @param $status
	 * 
	 */
	public function updateUserCatalogueItem($username, $itemId, $rating, $status, $epsWatched){
		$entry = array(
				'id' => $itemId,
				'rating' => $rating,
				'status' => $status,
				'episodesWatched' => $epsWatched,
				'date_updated' => date('m/d/Y h:i:s a')
		);

		$user = User::where('username', '=', "$username")->firstOrFail();
		$userCat = $user->catalogueItems; 
		$userCat[$itemId] = $entry;
		$user->catalogueItems = $userCat;
		return $user->save() ? 'true' : 'false';
	}

	/**
	 * Removes the given catalogue item in the username's catalogueItems array
	 * @param $username
	 * @param $itemId
	 */
	public function removeFromUserCatalogue($username, $itemId){
		$user = User::where('username', '=', "$username")->firstOrFail();
		$userCat = $user->catalogueItems; 
		unset($userCat[$itemId]);
		$user->catalogueItems = $userCat;
		return $user->save() ? 'true' : 'false';
	}

	/**
	 * Checks if an item is in a user's catalogue
	 * @param $username
	 * @param $itemId
	 */
	public function inUserCatalogue($username, $itemId){
		$user = User::where('username', '=', "$username")->firstOrFail();
		return isset($user->catalogueItems[$itemId]) ? 'true' : 'false';
	}

	/**
	 * Adds an item to the user's favorites array
	 * @param $username
	 * @param $itemId
	 */
	public function addToUserFavorites($username, $itemId){
		$user = User::where('username', '=', "$username")->firstOrFail();
		$userFavs = $user->favorites;
		$userFavs[$itemId] = $itemId;
		$user->favorites = $userFavs;
		return $user->save() ? 'true' : 'false';
	}

	/**
	 * Removes an item to the user's favorites array if it exists
	 * @param $itemId
	 */
	public function removeFromUserFavorites($username, $itemId){
		$user = User::where('username', '=', "$username")->firstOrFail();
		$userFavs = $user->favorites;
		if(isset($userFavs[$itemId])){
			unset($userFavs[$itemId]);	
			$user->favorites = $userFavs;
			return $user->save() ? 'true' : 'false';
		}
		else return 'false';
	}

	/**
	 * Checks if an item is in a user's favorites
	 * @param $username
	 * @param $itemId
	 */
	public function inUserFavorites($username, $itemId){
		$user = User::where('username', '=', "$username")->firstOrFail();
		return isset($user->favorites[$itemId]) ? 'true' : 'false';
	}
}
