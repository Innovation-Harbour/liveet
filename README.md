# Liveet

## Production

## TODO

- deleting a ticket type should delete all its access codes
- check event time and event controls before doing anything with its ticket type, ticket and access codes

## PONDERS

- should assigned access codes be deletable
- should assigned tickets be deletable
- should a ticket type that has some assigned tickets be modifiable
- how do we manage an access code batch

## LAST THINKING

- does get access codes, tickets or event by an organiser, gets theirs or general

## TO-CHECK

```
			$data->username = trim($data->username);
			$arrsUnallowed = array("admin", "administrator", "username", "social", "intagram", "facebook", "twitter", "error");
			if (isset($data->username) and isset($data->email) and isset($data->fullname) and isset($data->password)) {
				if (!$dbn->isExistV2("select*from customers where username = ?", array($data->username)) and !preg_match('/[^a-z_\-0-9]/i', $data->username)) {
					if (!$dbn->isExistV2("select*from customers where email = ?", array($data->email))) {
						if (strlen($data->username) >= 5) {
							if (!in_array($data->username, $arrsUnallowed)) {
								$usernames = explode(" ", $data->username);
								if (count($usernames) == 1) {
									$email = filter_var($data->email, FILTER_SANITIZE_EMAIL);
									$domains = explode("@", $email);
									if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
										$domain = $domains[1];
										if (checkdnsrr($domain, 'MX')) {
											if (strlen($data->password) > 6)

```
