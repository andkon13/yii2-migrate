миграции используют webtoucher/yii2-migrate для разделения миграций на модули
uses:

class m000000_000000_users extends \andkon\migrate\Migration
{
    public function setTables()
    {
        return [
            'users' => [
                'id'            => $this->primaryKey(),
                'company_id'    => $this->integer()->notNull(),
                'position_id'   => $this->integer(),
                'department_id' => $this->integer(),
                'login'         => $this->string(255)->notNull(),
                'password'      => $this->string(255),
                'password_salt' => $this->string(255),
                'first_name'    => $this->string(255),
                'middle_name'   => $this->string(255),
            ]
        ];
    }
    
    public function setForeignKeys()
    {
        return [
            // user
            [
                'user'    => 'company_id',
                'company' => 'id',
            ],
            [
                'user'     => 'position_id',
                'position' => 'id',
                'delete'   => 'RESTRICT',
            ],