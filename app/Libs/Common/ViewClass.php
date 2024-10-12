<?php
    namespace App\Libs\Common;

    use Illuminate\Support\Facades\Blade;
    use Symfony\Component\Debug\Exception\FatalThrowableError;

    class ViewClass
    {
        function render(string $body, array $data = [])
        {
            app(\Illuminate\Contracts\View\Factory::class)
                ->share('errors', app(\Illuminate\Support\MessageBag::class));
            extract(app('view')->getShared(), EXTR_SKIP);
            extract($data, EXTR_SKIP);
            $php = Blade::compileString($body);

            $__env->incrementRender();


            $obLevel = ob_get_level();
            ob_start();

            try {
                eval('?' . '>' . $php);
            } catch (Exception $e) {
                while (ob_get_level() > $obLevel) ob_end_clean();
                throw $e;
            } catch (Throwable $e) {
                while (ob_get_level() > $obLevel) ob_end_clean();
                throw new FatalThrowableError($e);
            }

            $__env->decrementRender();
            $__env->flushStateIfDoneRendering();
            return ob_get_clean();
        }
    }
