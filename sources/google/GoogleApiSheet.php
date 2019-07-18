<?php


namespace vnukga\migrationGenerator\sources\google;

use Google_Client;
use Google_Service_Sheets;

class GoogleApiSheet
{
    public $status_column = 'status';

    public $table_column = 'table';

    public $attr_column = 'attr';

    public $description_column = 'description';

    public $type_column = 'type';

    public $index_column = 'index';

    public $default_column = 'default';

    public $null_column = 'null';

    public $linked_table_column = 'linked_table';

    public $keyPath;

    /**
     * Returns array from Google Sheet with $spreadsheetId.
     * @param string $spreadsheetId
     * @return array
     * @throws \Google_Exception
     */
    public function getPreparedArray(string $spreadsheetId)
    {
        $client = $this->getClient();
        $service = new Google_Service_Sheets($client);
        $range = 'DB';
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->values;
        $table_map = $this->getTableMap($values[0]);
        $result = $this->valuesToArray($values, $table_map);
        return $result;
    }

    private function getTableMap(array $values)
    {
        $schema = [
            'status' => $this->getKeyForHeader($this->status_column, $values),
            'table' => $this->getKeyForHeader($this->table_column, $values),
            'attr' => $this->getKeyForHeader($this->attr_column, $values),
            'description' => $this->getKeyForHeader($this->description_column, $values),
            'type' => $this->getKeyForHeader($this->type_column, $values),
            'index' => $this->getKeyForHeader($this->index_column, $values),
            'default' => $this->getKeyForHeader($this->default_column, $values),
            'null' => $this->getKeyForHeader($this->null_column, $values),
            'linked_table' => $this->getKeyForHeader($this->linked_table_column, $values),
        ];
        return $schema;
    }

    /**
     * Returns key of a value equals to $header.
     * @param string $header
     * @param array $values
     * @return int|string|null
     */
    private function getKeyForHeader(string $header, array $values)
    {
        foreach ($values as $key => $value){
            if($value == $header){
                return $key;
            }
        }
        return null;
    }

    /**
     * Leads values to standart schema
     * @param array $values
     * @param array $schema
     * @return array
     */
    private function valuesToArray(array $values, array $schema)
    {
        $result = [];

        for ($i = 1; $i < count($values); $i++){
            $value = $values[$i];
            if($value[$schema[$this->table_column]] != null){
                $table_name = $value[$schema[$this->table_column]];
                continue;
            }

            if($value[$schema[$this->attr_column]] != null){
                $column_name = $value[$schema[$this->attr_column]];
                $result[$table_name][$column_name] = [
                    'type' => $value[$schema[$this->type_column]],
                    'comment' => $value[$schema[$this->description_column]],
                    'index' => $value[$schema[$this->index_column]],
                    'required' => $value[$schema[$this->null_column]] == true,
                    'defaultValue' => $value[$schema[$this->default_column]],
                    'foreignKey' => $value[$schema[$this->linked_table_column]],
                ];
            }
        }
        return $result;
    }

    /**
     * Returns Google API's client.
     * @return Google_Client
     * @throws \Google_Exception
     */
    private function getClient()
    {
        $client = new Google_Client();
        $client->setApplicationName('Google Sheets API PHP Quickstart');
        $client->setScopes([Google_Service_Sheets::SPREADSHEETS]);
        $client->setAuthConfig($this->keyPath . '/credentials.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        $tokenPath = $this->keyPath . '/token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }
        return $client;
    }

}