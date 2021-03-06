<?php

/**
 * Interface definition for BNote App (BNA) connection.
 * @author Matti
 *
 */
interface iBNA {
	
	/**
	 * @return Returns all rehearsals.
	 */
	public function getRehearsals();
	
	/**
	 * Retrieves all rehearsals for a user and whether he/she participates or not.
	 * @param Integer $user ID of the user.
	 * @return All rehearsal with participation information for the user.
	 */
	public function getRehearsalsWithParticipation($user);

	/**
	 * @return Returns all concerts.
	 */
	public function getConcerts();
	
	/**
	 * @return Returns all contacts.
	 */
	public function getContacts();
	
	/**
	 * @return Returns all locations.
	 */
	public function getLocations();
	
	/**
	 * @return Returns all tasks for this user.
	 */
	public function getTasks();
	
	/**
	 * @return Returns the news.
	 */
	public function getNews();
	
	/**
	 * @return Returns all votes for this user.
	 */
	public function getVotes();
	
	/**
	 * Retrieves all possible options for the given vote.
	 * @param Integer $vid Vote ID.
	 * @return All vote options for this vote.
	 */
	public function getVoteOptions($vid);
	
	/**
	 * @return Returns all songs in the repertoire.
	 */
	public function getSongs();
	
	/**
	 * @return Returns all genres.
	 */
	public function getGenres();
	
	/**
	 * @return Returns all statuses.
	 */
	public function getStatuses();
	
	/**
	 * <b>Use this function only to fetch all data for a user once!</b>
	 * @return Returns all rehearsals, concerts, contacts, location and so on.
	 */
	public function getAll();
	
	/**
	 * Retrieves all comments for the given object.
	 * @param char $otype R=Rehearsal, C=Concert, V=Vote
	 * @param Integer $oid ID of the object to comment on.
	 * @return All comments for the given object.
	 */
	public function getComments($otype, $oid);
	
	/**
	 * Gets the participation choice of a user for a rehearsal.
	 * @param Integer $rid Rehearsal ID
	 * @param Integer $uid User ID
	 * @return 1 if the user participates, 0 if not, -1 if not chosen yet.
	 */
	public function getParticipation($rid, $uid);
	
	/**
	 * Saves the participation of a user in a rehearsal.
	 * @param Integer $rid Rehearsal ID
	 * @param Integer $uid User ID
	 * @param Integer $part 1=participates, 0=does not participate, 2=maybe participates
	 * @param String $reason Optional parameter to give a reason for not participating.
	 */
	public function setParticipation($rid, $uid, $part, $reason);
	
	/**
	 * Set a task as completed. (POST)
	 * @param int $tid Task ID.
	 */
	public function taskCompleted($tid);
	
	/**
	 * Adds a song to the repertoire. (POST)
	 * @param String $title Title of the song.
	 * @param String $length Lenght in format mm:ss.
	 * @param Integer $bpm Beats per Minute.
	 * @param String $music_key Musical key of the song.
	 * @param String $notes Additional Notes to the song.
	 * @param Integer $genre Genre ID.
	 * @param String $composer Name of the composer.
	 * @param Integer $status Status ID.
	 * @return The ID of the new song.
	 */
	public function addSong($title, $length, $bpm, $music_key, $notes, $genre, $composer, $status);
	
	/**
	 * Adds a rehearsal. (POST)
	 * @param String $begin Begin of the rehearsal, format: YYYY-MM-DD HH:ii:ss.
	 * @param String $end End of the rehearsal, format: YYYY-MM-DD HH:ii:ss.
	 * @param String $approve_until Approve participation until, format: YYYY-MM-DD HH:ii:ss.
	 * @param String $notes Notes for the rehearsal.
	 * @param Integer $location Location ID.
	 * @return The ID of the new rehearsal.
	 */
	public function addRehearsal($begin, $end, $approve_until, $notes, $location);
	
	/**
	 * Adds a vote to the voting. (POST)
	 * @param Integer $vid ID of the voting.
	 * @param Array $options Options in format: [vote_option id] => [0 as no, 1 as yes, 2 as maybe].
	 */
	public function vote($vid, $options);
	
	/**
	 * Adds a comment to an object. (POST)
	 * @param char $otype R=Rehearsal, C=Concert, V=Vote
	 * @param Integer $oid ID of the object to comment on.
	 * @param String $message Urlencoded message.
	 * @return ID of the newly created comment.
	 */
	public function addComment($otype, $oid, $message); 
}

// Abstract Implementation of BNote Application Interface
include "bna-abstract.php";

?>