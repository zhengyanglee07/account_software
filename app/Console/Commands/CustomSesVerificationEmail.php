<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Aws\Laravel\AwsFacade;
use Aws\Ses\Exception\SesException;
use Aws\Exception\AwsException;

class CustomSesVerificationEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ses:custom';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or update custom SES verification email template';

    /**
     * Custom email template configs
     *
     * @var array
     */
    protected $templateConfigs;

    /**
     * Aws SES client
     *
     * @var \Aws\AwsClientInterface
     */
    protected $ses;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $appUrl = 'https://my.hypershapes.com';

        $this->templateConfigs =
            [
                // stable (my.hypershapes.com)
                [
                    'TemplateName' => 'hypershapes-email-verification-template',
                    'TemplateSubject' => 'Please confirm your email address',
                    'FromEmailAddress' => 'mail@hypershapes.com',
                    'TemplateContent' => view('emailTemplates.sesCustomVerificationEmail.template'),
                    'SuccessRedirectionURL' => 'https://my.hypershapes.com/emails/verification/success',
                    'FailureRedirectionURL' => 'https://my.hypershapes.com/emails/verification/failed'
                ],

                // staging (staging.hypershapes.com)
                [
                    'TemplateName' => 'hypershapes-email-verification-template--staging',
                    'TemplateSubject' => 'Please confirm your email address',
                    'FromEmailAddress' => 'mail@hypershapes.com',
                    'TemplateContent' => view('emailTemplates.sesCustomVerificationEmail.template'),
                    'SuccessRedirectionURL' => 'https://staging.hypershapes.com/emails/verification/success',
                    'FailureRedirectionURL' => 'https://staging.hypershapes.com/emails/verification/failed'
                ],

                // local (.test variant)
                // Unfortunately *.test is an invalid URL by AWS
                // [
                //     'TemplateName' => 'hypershapes-email-verification-template--local-test',
                //     'TemplateSubject' => 'Please confirm your email address',
                //     'FromEmailAddress' => 'mail@hypershapes.com',
                //     'TemplateContent' => view('emailTemplates.sesCustomVerificationEmail.template'),
                //     'SuccessRedirectionURL' => 'http://hypershapes.test/emails/verification/success',
                //     'FailureRedirectionURL' => 'http://hypershapes.test/emails/verification/failed'
                // ],

                // local (.dev variant)
                [
                    'TemplateName' => 'hypershapes-email-verification-template--local-dev',
                    'TemplateSubject' => 'Please confirm your email address',
                    'FromEmailAddress' => 'mail@hypershapes.com',
                    'TemplateContent' => view('emailTemplates.sesCustomVerificationEmail.template'),
                    'SuccessRedirectionURL' => 'http://hypershapes.dev/emails/verification/success',
                    'FailureRedirectionURL' => 'http://hypershapes.dev/emails/verification/failed'
                ],
            ];

        $this->ses = AwsFacade::createClient('ses');
    }

    /**
     * Create custom verification email template. Might throw error if
     * the custom verification email is created
     *
     * @param array $templateConfig
     * @return bool
     */
    private function createCustomSesVerificationEmailTemplate(array $templateConfig): bool
    {
        try {
            $this->ses->createCustomVerificationEmailTemplate($templateConfig);
            echo 'Successfully created ' . $templateConfig['TemplateName'] . "\n";

            $success = true;
        } catch (SesException $ex) {
//            echo $ex->getMessage();
            $success = false;
        } catch (AwsException $ex) {
//            echo $ex->getMessage();
            $success = false;
        }

        return $success;
    }

    /**
     * Try to update custom verification email template if failed to create
     *
     * @param array $templateConfig
     * @return void
     */
    private function updateCustomVerificationEmailTemplate(array $templateConfig): void
    {
        try {
            $this->ses->UpdateCustomVerificationEmailTemplate($templateConfig);
            echo 'Successfully updated ' . $templateConfig['TemplateName'] . "\n";
        } catch (SesException $ex) {
            echo $ex->getMessage();
        } catch (AwsException $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        foreach ($this->templateConfigs as $templateConfig) {
            $successfullyCreated = $this->createCustomSesVerificationEmailTemplate($templateConfig);

            if (!$successfullyCreated) {
                $this->updateCustomVerificationEmailTemplate($templateConfig);
            }
        }
    }
}
