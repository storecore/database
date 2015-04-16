<?php
namespace StoreCore\Database;

/**
 * Translation Memory Model
 *
 * @author    Ward van der Put <Ward.van.der.Put@gmail.com>
 * @copyright Copyright (c) 2015 StoreCore
 * @license   http://www.gnu.org/licenses/gpl.html
 * @package   StoreCore\Database
 * @version   0.0.1
 */
class TranslationMemory extends \StoreCore\AbstractModel
{
    /**
     * @type string VERSION
     */
    const VERSION = '0.0.1';

    /**
     * @type int $LanguageID
     * @type int $ParentLanguageID
     */
    private $LanguageID = 2057;
    private $ParentLanguageID = 2057;

    /**
     * @type null|array $Translations
     */
    private $Translations;

    /**
     * Load translations as name/value pairs.
     *
     * @param int|string $language_code
     *     Internal language identifier (integer) or ISO language code (string).
     *
     * @return array
     */
    public function getTranslations($language_code = null, $storefront = true)
    {
        if ($language_code !== null) {
            $this->setLanguage($language_code);
        }

        $storefront = (bool)$storefront;

        // Populate with British English (2057) as the root language
        if ($this->Translations === null || $this->LanguageID == 2057) {
            $this->Translations = array();
            $this->readTranslations(2057, $storefront);
        }

        if ($this->LanguageID != 2057) {
            if ($this->ParentLanguageID != 2057 && $this->ParentLanguageID != $this->LanguageID) {
                $this->readTranslations($this->ParentLanguageID, $storefront);
            }
            $this->readTranslations($this->LanguageID, $storefront);
        }

        return $this->Translations;
    }

    /**
     * @param int $language_id
     * @param bool $storefront
     * @return void
     */
    private function readTranslations($language_id, $storefront)
    {
        $sql = "SELECT SQL_NO_CACHE CONCAT('STORECORE_I18N_', translation_id) AS name, translation AS value FROM sc_translation_memory WHERE language_id = " . (int)$language_id;
        if ($storefront) {
            $sql .= ' AND is_admin_only = 0';
        }
        $sql .= ' ORDER BY translation_id ASC';

        if ($this->Registry->has('Connection')) {
            $dbh = $this->Registry->get('Connection');
        } else {
            $dbh = new \StoreCore\Database\Connection();
            $this->Registry->set('Connection', $dbh);
        }

        $stmt = $dbh->prepare($sql);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $this->Translations[$row['name']] = $row['value'];
        }
    }

    /**
     * Set the language to load.
     *
     * @param int|string $language_code
     *     Internal language identifier (integer) or ISO language code (string).
     *
     * @return bool
     *     Returns true if the language was set or false if the language does
     *     not exist or is not enabled.
     */
    public function setLanguage($language_code)
    {
        if ($language_code == $this->LanguageID) {
            return true;
        }

        $sql = 'SELECT language_id, parent_id FROM sc_languages WHERE status = 1 ';
        if (is_string($language_code)) {
            $language_code = str_ireplace('_', '-', $language_code);
            if (strlen($language_code) == 2) {
                $language_code = strtolower($language_code);
                $sql .= "AND iso_code LIKE '" . $language_code . "%' ORDER BY parent_id <> language_id LIMIT 1";
            } else {
                $sql .= "AND iso_code = '" . $language_code . "'";
            }
        } elseif (is_int($language_code)) {
            $sql .= 'AND language_id = ' . $language_code;
        } else {
            return false;
        }

        if ($this->Registry->has('Connection')) {
            $dbh = $this->Registry->get('Connection');
        } else {
            $dbh = new \StoreCore\Database\Connection();
            $this->Registry->set('Connection', $dbh);
        }

        $stmt = $dbh->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row === false) {
            return false;
        } else {
            $this->LanguageID = (int)$row['language_id'];
            $this->ParentLanguageID = (int)$row['parent_id'];
            return true;
        }
    }
}
