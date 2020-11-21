<?php
namespace {   
    use Cimply\Core\Annotation\Annotation as Annotate;
    trait Annotation {

        public static function GetAnnotations($classObject = null): Annotate {
            $objectExpl = explode("::", $classObject);
            $className = $objectExpl[0] ?? null;
            $method = $objectExpl[1] ?? null;;
            try {
                $annotate = new Annotate($className, $method);
            } catch (\Exception | \ReflectionClass | \ReflectionMethod | \ReflectionProperty | \AnnotateException | ArgumentCountError $e) {
                \Debug::VarDump($e->getMessage());
            }		
            return $annotate;
        }
    }
}