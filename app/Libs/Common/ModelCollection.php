<?php
    namespace App\Libs\Common;



    use Illuminate\Database\Eloquent\Collection;

    class ModelCollection extends Collection
    {
        public function keyValue($id, $value)
        {
            return $this->mapWithKeys(function ($v, $k) use($id, $value) {
                return [$v[$id] => $v[$value]];
            });
        }

        public function checkBox($value)
        {
            $results = [];

            foreach ($this as $item){
                $results[] = $item->$value;
            }
            return $results;
        }

        public function groupChildren(string $type, array $types)
        {
            foreach ($this as $key => $item){
                foreach ($types as $typeOwn){
                    $children[$typeOwn] = [];
                }

                foreach ($item->children as $child){
                    $children[$child->$type][] = $child;
                }

                $typeKey = "children_" . $type;
                $this[$key]->$typeKey = $children;
            }

            return $this;
        }

        public function medias()
        {
            foreach ($this as $index => $item){
                $item->medias();
            }
        }

    }
