<?php
	/*
	 * Score submission.
	 */
	require_once("../inc/functions.php");

	try
	{
		// Output variables if we need it
		if ($SUBMIT["outputParams"])
			outputVariable("submit-vars.txt", $_POST);

		// Check if everything is set
		if (!isset($_POST["score"]) || !isset($_POST["iv"]) || !isset($_POST["pass"]) || empty($_POST["score"]) || empty($_POST["iv"]) || empty($_POST["pass"]) ) {
			throw new Exception("beatmap");
		}

		// Check if game is in maintenance
		if (checkGameMaintenance()) {
			// Return error: pass so the user knows that his score was not submitted
			throw new Exception("pass");
		}

		// Decrypt score data, returns a string with all the score data separed by colons
		$scoreData = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $SUBMIT["AESKey"], base64_decode($_POST["score"]), MCRYPT_MODE_CBC, base64_decode($_POST["iv"]));

		// Explode the decrypted score string
		$scoreDataArray = explode(":", $scoreData);

		// Get username
		$username = $scoreDataArray[1];

		// Check if the user/password is correct
		if (!checkOsuUser($username, $_POST["pass"])) {
			throw new Exception("pass");
		}

		// Check if we are banned/pending activation
		switch(getUserAllowed($username))
		{
			case 0: throw new Exception("pass"); break;	// We are banned, error: pass (we don't user error: ban to avoid sending fake data to bancho)
			case 2: $GLOBALS["db"]->execute("UPDATE users SET allowed = 1 WHERE username = ?", $username); break; // We are not banned but our account still needs to be activated, activate it.
		}

		// If we have completed a song, $_POST["x"] is not set (completed value in db is 2).
		// If we have failed/retried, $_POST["x"] is 0/1 (completed value in db is 0/1).
		if (!isset($_POST["x"]))
		{
			// We've finished a song
			// Save score (and increase playcount)
			$replayID = saveScore($scoreDataArray);

			// Save replay if we played in rankable mods
			if (isRankable($scoreDataArray[13]))
				saveReplay($replayID);

			// Done
			echo($SUBMIT["okOutput"]);
		}
		else
		{
			// We have failed/retried, save score (only if we want it) and increase playcount
			saveScore($scoreDataArray, $_POST["x"], $SUBMIT["saveFailedScores"], true);

			// Done
			echo($SUBMIT["okOutput"]);
		}
	}
	catch (Exception $e)
	{
		// Error
		echo("error: ".$e->getMessage());
	}

?>