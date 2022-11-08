<?php
class generateDownloads extends common {
    protected $user_id;
    protected $type;
    protected $slug;

    public $token;

    function __construct($user_id=null, $type=null, $slug=null)
    {
        $this->user_id = $user_id;
        $this->type = $type;
        $this->slug = $slug;
    }

    public function generateToken() {
        session_start();
        $_SESSION['user'] = $this->user_id;
        $array['user_id'] = $this->user_id;
        $array['type'] = $this->type;
        $array['slug'] = $this->slug;
        $array['token'] = strtolower($this->confirmToken($this->createRandomPassword(32)));

        $replace[] = "user_id";
        $replace[] = "slug";
        $replace[] = "type";
        $replace[] = "token";

        $this->replace(table_name_prefix . "downloads", $array, $replace);

        return $array['token'];
    }

    private function confirmToken( $key ) {
        if ($this->checkExixst(table_name_prefix."downloads", "token", $key, "token", "token") == 0) {
            return $key;
        } else {
            return $this->confirmToken($this->createRandomPassword(50));
        }
    }

    public function removeToken() {
        return $this->delete(table_name_prefix . "downloads", $this->token, "token");
    }

    public function getToken() {
        return $this->getOne(table_name_prefix."downloads", $this->token, "token");
    }

    public function validateAccess($token) {
        print_r($_SESSION);
    }
}
?>