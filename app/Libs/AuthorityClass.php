<?php
    namespace App\Libs;


    use App\Models\Authority\Authority;
    use App\Models\Authority\AuthorityChild;
    use Illuminate\Support\Facades\Auth;
    use phpDocumentor\Reflection\Types\Boolean;

    class AuthorityClass{

        public function authority(string $page)
        {

            if (!$this->isAuthority($page)){
                exit();
            }
        }

        public function isAuthority(string $page) : bool
        {
            $user = Auth::user();

            if (!$user->authority_id){
                return true;
            }

            $authority = AuthorityChild::where("authority_id", $user->authority_id)->whereType($page)->whereValue(1)->first();
            if ($authority){
                return true;
            }

            return false;
        }

    }
