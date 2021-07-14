- Instalar o projeto: laravel new nomeDoProjeto
- Configurar o .env: dados do banco de dados 
- Configurar o config/database.php: colocar o BD como utf8 e utf8_general_ci
- Criar as rotas em routes/api.php: (rota teste: Route::get('/ping', function(){return ['pong'=>true]}))
- Iniciar o servidor: php artisan server
- Testar a rota com o https://resttesttest.com/
- Instalar o JWT em https://jwt-auth.readthedocs.io/ 
    - Install via composer
    - Publish the config
    - Generate secret key
- Colocar a expiração do jwt para null, no .env: JWT_TTL=NULL
- Remover exp da required_claims list dentro de jwt.php conforme documentação jwt-auth:
    - Notice: If you set this to null you should remove 'exp' element from 'required_claims' list.
- No arquivo config/auth.php, alterar os campos pra ficar como abaixo:
    'defaults' => [
        'guard' => 'api', // <=== Alterar para api
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'jwt', // <=== Alterar para jwt
            'provider' => 'users',
            'hash' => false,
        ],
    ],
- Alterar o model Users conforme documentação jwt-auth quick start:
    use Tymon\JWTAuth\Contracts\JWTSubject;
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
- Testar novamente o ping no rest e pronto!
