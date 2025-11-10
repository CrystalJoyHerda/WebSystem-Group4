
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPasswordToUsers extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('users')) return;

        $fields = [
            'password' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ];
        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        if (! $this->db->tableExists('users')) return;
        $this->forge->dropColumn('users', 'password');
    }
}