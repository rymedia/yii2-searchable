<?php
/**
 * @link https://github.com/vuongxuongminh/yii2-searchable
 * @copyright Copyright (c) 2019 Vuong Xuong Minh
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace vxm\searchable;

use Yii;

use yii\base\Configurable;

use TeamTNT\TNTSearch\TNTSearch as BaseTNTSearch;

use PDO;

/**
 * Class TNTSearch base on [[\TeamTNT\TNTSearch\TNTSearch]] implementing yii configurable.
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0.0
 */
class TNTSearch extends BaseTNTSearch implements Configurable
{
    /**
     * @inheritDoc
     */
    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            Yii::configure($this, $config);
        }

        parent::__construct();
    }

    /**
     * @param      $keyword
     * @param bool $isLastWord
     *
     * @return array
     */
    public function getWordlistByKeyword($keyword, $isLastWord = false)
    {
        if ($this->fuzziness) {
            return $this->fuzzySearch($keyword);
        }

        $searchWordlist = "SELECT * FROM wordlist WHERE term like :keyword LIMIT 1";
        $stmtWord       = $this->index->prepare($searchWordlist);

        if ($this->asYouType && $isLastWord) {
            $searchWordlist = "SELECT * FROM wordlist WHERE term like :keyword ORDER BY length(term) ASC, num_hits DESC LIMIT 1";
            $stmtWord       = $this->index->prepare($searchWordlist);
            $stmtWord->bindValue(':keyword', mb_strtolower($keyword)."%");
        } else {
            $stmtWord->bindValue(':keyword', mb_strtolower($keyword));
        }
        $stmtWord->execute();
        $res = $stmtWord->fetchAll(PDO::FETCH_ASSOC);

        if ($this->fuzziness && !isset($res[0])) {
            return $this->fuzzySearch($keyword);
        }
        return $res;
    }

}
