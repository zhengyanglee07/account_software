<?php

namespace App\Services;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Http;

/**
 * A simple service to use mjml binary in node_modules/.bin
 * and compile MJML string into html
 *
 * Most of the code here refer to:
 * https://github.com/qferr/mjml-php/blob/master/src/Mjml/Renderer/BinaryRenderer.php
 *
 * The reason why I don't just use that package above, no
 * special reason, just because I don't want to add one more
 * composer dependency just to use this short function
 *
 * Class MjmlRendererService
 * @package App\Services
 */
class MjmlRendererService
{
    private $bin;

    public function __construct()
    {
        $this->bin = base_path() . '/node_modules/.bin/mjml';
    }

    /**
     * Render an email html from the $mjmlContent string provided
     *
     * For details refer to:
     * - cli: https://mjml.io/documentation/#command-line-interface
     * - mjml string: https://mjml.io/documentation/#inside-node-js
     *
     *
     * @param string $mjmlContent
     * @return string
     * @throws \Exception
     */
    public function render(string $mjmlContent): string
    {
        return $this->httpRender($mjmlContent);
    }

    /**
     * Local render using MJML command line utils std output
     *
     * Note:
     * the rendered output most probably will contain a commented
     * <!-- FILE: undefined --> at the top, this is perfectly fine,
     * looks like it's a mjml bug.
     *
     * @param string $mjmlContent
     * @return string
     */
    public function localRender(string $mjmlContent): string
    {
        $arguments = [
            $this->bin,
            '-i',
            '-s',
            //            '--config.minify'
        ];

        $process = new Process($arguments);
        $process->setInput($mjmlContent);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            throw new \RuntimeException('Unable to compile MJML in local render. Stack error: ' . $e->getMessage());
        }

        return $process->getOutput();
    }

    /**
     * Render remotely via an API endpoint
     *
     * @param string $mjmlContent
     * @return mixed
     * @throws \Exception
     */
    public function httpRender(string $mjmlContent)
    {
        try {
            $response = Http::withBasicAuth(
                config('mail.mjml_application_id'),
                config('mail.mjml_secret_key')
            )->post(config('mail.mjml_endpoint'), [
                'mjml' => $mjmlContent
            ]);

            return $response->json()['html'];
        } catch (\Exception $e) {
            throw new \RuntimeException('Unable to compile MJML in http. Stack error: ' . $e->getMessage());
        }
    }
}
