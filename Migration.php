<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 08.09.15
 * Time: 15:37
 */

namespace andkon\migrate;

/**
 * Class Migrate
 */
class Migration extends \webtoucher\migrate\components\Migration
{
    /** @var string */
    protected $tableOptions = 'ENGINE=InnoDB DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci';
    /** @var array */
    private $tables = [];
    /** @var array */
    private $fk = [];
    /** @var array */
    private $fields = [];
    /** @var array */
    private $indexes = [];

    /**
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->tables  = $this->setTables();
        $this->fields  = $this->setFields();
        $this->fk      = $this->setForeignKeys();
        $this->indexes = $this->setIndexes();
    }

    /**
     * Назначает таблицы для их создания при UP/удалени при DOWN
     * <code>
     * [
     *      'table1' =>
     *      [
     *          'id' => $this->primaryKey(),
     *          'name' => $this->string(255)->notNull(),
     *          ...
     *      ]
     *];
     * </code>
     *
     * @return array
     */
    public function setTables()
    {
        return [];
    }

    /**
     * Добавляем поля к табличам
     * <code>
     * [
     *      table => [
     *          [fieldName => type],
     *          [fieldName => type],
     *      ]
     * ]
     * </code>
     *
     * @return array
     */
    protected function setFields()
    {
        return [];
    }

    /**
     * Устанавливает внешние ключи которые будут добавлены/удалены при up/down
     * <code>
     * [
     *      [
     *          'tableFrom' => 't2_id',
     *          'tableTo' => 'id'
     *      ],
     *      [
     *          'tableFrom2'=> 'parent_id',
     *          'self' => 'id', // self - ссылка на эту-же таблицу
     *          'delete' => 'CASCADE',// default
     *          'update' => 'NO ACTION'// default
     *      ],
     * ]
     * </code>
     *
     * @return array
     */
    public function setForeignKeys()
    {
        return [];
    }

    /**
     * Добавляет/убирает записи в бд
     * <code>
     *  return [
     *      'tableName' => [
     *          ['id' => 1, 'name' => 'example1', 'type' => 1],
     *          ['id' => 2, 'name' => 'example2', 'type' => 2],
     *          ...
     *      ]
     * ];
     * </code>
     *
     * @return array
     */
    public function setValues()
    {
        return [];
    }

    /**
     * Добавляет/убирает индексы
     * <code>
     * return [
     *             'index_name_0'   => ['tableName', 'field'],
     *             'index_name_1' => ['tableName', ['field0', 'field1', ...], $isUnique],
     *         ];
     * </code>
     *
     * @return array
     */
    public function setIndexes()
    {
        return [];
    }

    /**
     * Применяет миграцию
     *
     * @return bool
     */
    public function safeUp()
    {
        try {
            $this->tableUp();
            $this->fieldsUp();
            $this->valUp();
            $this->fkUp();
            $this->indexesUp();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Создает таблицы
     *
     * @return void
     */
    protected function tableUp()
    {
        foreach ($this->tables as $tableName => $fields) {
            $this->createTable($tableName, $fields, $this->tableOptions);
        }
    }

    /**
     * Добавляет поля
     *
     * @return void
     */
    protected function fieldsUp()
    {
        foreach ($this->fields as $table => $fields) {
            foreach ($fields as $fieldsName => $type) {
                $this->addColumn($table, $fieldsName, $type);
            }
        }
    }

    /**
     * создает внешние ключи
     *
     * @return void
     */
    protected function fkUp()
    {
        foreach ($this->fk as $fk) {
            $name   = $this->getFkName($fk);
            $tables = array_keys($fk);
            $keys   = $tables;
            if ($tables[1] === 'self') {
                $tables[1] = $tables[0];
            }

            $fk = array_merge(['delete' => 'CASCADE', 'update' => 'NO ACTION'], $fk);
            $this->addForeignKey(
                $name,
                $tables[0],
                $fk[$keys[0]],
                $tables[1],
                $fk[$keys[1]],
                $fk['delete'],
                $fk['update']
            );
        }
    }

    /**
     * инсертит данные
     *
     * @return bool
     */
    protected function valUp()
    {
        $data = $this->setValues();
        if (count($data)) {
            foreach ($data as $tabName => $valArray) {
                foreach ($valArray as $item) {
                    $this->insert($tabName, $item);
                }
            }
        }

        return true;
    }

    /**
     * Генегирует имя связи
     *
     * @param array $fk описание связи
     *
     * @return string
     */
    protected function getFkName($fk)
    {
        $name = implode('_', array_merge(array_keys($fk), $fk));

        return $name;
    }

    /**
     * Добавляет индексы
     *
     * @return bool
     */
    protected function indexesUp()
    {
        foreach ($this->indexes as $indexName => $params) {
            $table    = $params[0];
            $columns  = $params[1];
            $isUnique = (array_key_exists(2, $params) ? $params[2] : false);
            $this->createIndex($indexName, $table, $columns, $isUnique);
        }

        return true;
    }

    /**
     * Откатывает миграцию
     *
     * @return bool
     */
    public function safeDown()
    {
        try {
            $this->indexexDown();
            $this->fkDown();
            $this->valDown();
            $this->fieldsDown();
            $this->tableDown();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @return void
     */
    protected function fkDown()
    {
        foreach ($this->fk as $fk) {
            $name = $this->getFkName($fk);
            $this->dropForeignKey($name, array_keys($fk)[0]);
        }
    }

    /**
     * Удаляет поля
     *
     * @return void
     */
    protected function fieldsDown()
    {
        foreach ($this->fields as $table => $fields) {
            foreach (array_keys($fields) as $fieldName) {
                $this->dropColumn($table, $fieldName);
            }
        }
    }

    /**
     * @return void
     */
    protected function tableDown()
    {
        foreach (array_keys($this->tables) as $tableName) {
            $this->dropTable($tableName);
        }
    }

    /**
     * Удаляет данные
     *
     * @return bool
     */
    protected function valDown()
    {
        $data = $this->setValues();
        if (count($data)) {
            foreach ($data as $tabName => $valArray) {
                foreach ($valArray as $item) {
                    $this->delete($tabName, $item);
                }
            }
        }

        return true;
    }

    /**
     * Убирает индексы
     *
     * @return bool
     */
    public function indexexDown()
    {
        foreach ($this->indexes as $indexName => $params) {
            $table = $params[0];
            $this->dropIndex($indexName, $table);
        }

        return true;
    }
}
