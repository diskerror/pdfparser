<?php

namespace Diskerror\PdfParser;

use Traversable;

trait TraversableTrait
{
    private const EXCLUDE = [
        'parent',
        'kids',
        'document',
        'dictionary',
        //        'content',
    ];

    /**
     * Required by the IteratorAggregate interface.
     * Values are returned in a foreach loop but are not settable.
     *
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return (function () {
            static $dummyProps = [
                'header' => null,
                'trailer' => null,
                'dictionary' => null,
                'content' => null,
                'metadata' => null,
                'details' => null,
            ];

            $properties = array_merge($dummyProps, get_object_vars($this));

            foreach ($properties as $k => $v) {
                if (in_array(strtolower($k), self::EXCLUDE)) {
                    continue;
                }

                $ku    = ucfirst($k);
                $mName = 'get' . $ku;
                if (method_exists($this, $mName)) {
                    $v = $this->{$mName}();
                }

                if ($v === null) {
                    continue;
                }

                if (is_string($v) && strlen($v) > 1024) {
                    $v = substr($v, 0, 1024) . ' ...';
                }

                // Does this cover all desired uses of $v?
                if (is_scalar($v) || (is_countable($v) && count($v) > 0)) {
                    yield $ku => $v;
                }
            }
        })();
    }

}
