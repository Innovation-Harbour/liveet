# Liveet

## Production

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
