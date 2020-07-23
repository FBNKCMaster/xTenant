<?php

namespace FBNKCMaster\xTenant\Console\Commands;

use Illuminate\Console\Command;

class CommandWeb extends Command
{
    protected $signature = '';
    public $webSessUID = null;

    public function __construct()
    {
        $this->signature .= '
                            {--q_hash= : Question hash id}
                            {--web_input= : Question input}
                            {--web_sess_uid= : Web session unique id}';

        parent::__construct();
    }

    private function getWebOptions()
    {
        $webSessUID = $this->option('web_sess_uid');
        if (!is_null($webSessUID)) {
            $this->webSessUID = $webSessUID;
            $qHash = $this->option('q_hash');
            $webInput = $this->option('web_input');
            if (!is_null($qHash) && !is_null($webInput)) {
                $qId = $this->webSessUID . $qHash;
                $this->put($qId, $webInput);
            }
        }
    }

    private function autoAnswerQuestion($question, array $choices = null, $default = null)
    {
        $qHash = md5($question);
        $qId = $this->webSessUID . $qHash;
        if (\Cache::has($qId)) {
            return $this->get($qId);
        }

        $output['question'] = $question;
        $output['q_hash'] = $qHash;

        if (!is_null($choices)) {
            $output['choices'] = $choices;
        }

        if (!is_null($default)) {
            $output['default'] = $default;
        }

        return abort(404, serialize($output));
    }

    private function wasOutput($string)
    {
        $oId = $this->webSessUID . md5($string);
        return \Cache::has($oId);
    }

    public function confirm($question, $default = false) {
        $this->getWebOptions();
        if (!is_null($this->webSessUID)) {
            return $this->autoAnswerQuestion($question, null, $default);
        } else {
            return parent::confirm($question, $default);
        }
    }

    public function ask($question, $default = NULL) {
        $this->getWebOptions();
        if (!is_null($this->webSessUID)) {
            return $this->autoAnswerQuestion($question, null, $default);
        } else {
            return parent::ask($question, $default);
        }
    }

    public function anticipate($question, $choices, $default = null) {
        $this->getWebOptions();
        if (!is_null($this->webSessUID)) {
            return $this->autoAnswerQuestion($question, $choices, $default);
        } else {
            return parent::anticipate($question, $choices, $default);
        }
    }

    public function secret($question, $fallback = true) {
        $this->getWebOptions();
        if (!is_null($this->webSessUID)) {
            return $this->autoAnswerQuestion($question);
        } else {
            return parent::secret($question, $fallback);
        }
    }

    public function choice($question, array $choices, $default = null, $attempts = null, $multiple = false) {
        $this->getWebOptions();
        if (!is_null($this->webSessUID)) {
            return $this->autoAnswerQuestion($question, $choices, $default);
        } else {
            return parent::choice($question, $choices, $default, $attempts, $multiple);
        }
    }
    
    public function info($string, $verbosity = null)
    {
        $this->line($string, 'info', $verbosity);
    }

    public function line($string, $style = null, $verbosity = null)
    {
        $this->getWebOptions();
        if (is_null($this->webSessUID) || !$this->wasOutput($string)) {
            if (!is_null($this->webSessUID)) {
                $this->put($this->webSessUID . md5($string), $string);
            }
            
            parent::line($string, $style, $verbosity);
        }// else {
            //parent::line('');
        //}
    }

    public function comment($string, $verbosity = null)
    {
        $this->line($string, 'comment', $verbosity);
    }

    public function question($string, $verbosity = null)
    {
        $this->line($string, 'question', $verbosity);
    }

    public function error($string, $verbosity = null)
    {
        $this->line($string, 'error', $verbosity);
    }

    public function warn($string, $verbosity = null)
    {
        if (is_null($this->webSessUID)) {
            if (! $this->output->getFormatter()->hasStyle('warning')) {
                $style = new \Symfony\Component\Console\Formatter\OutputFormatterStyle('yellow');
    
                $this->output->getFormatter()->setStyle('warning', $style);
            }
        }

        $this->line($string, 'warning', $verbosity);
    }

    public function alert($string)
    {
        $this->getWebOptions();
        if (is_null($this->webSessUID) || !$this->wasOutput($string)) {
            if (!is_null($this->webSessUID)) {
                $this->put($this->webSessUID . md5($string), $string);
            }
            
            return parent::alert($string);
        }
    }

    public function call($command, array $arguments = []) {
        $this->getWebOptions();
        if (!is_null($this->webSessUID)) {
            $args = '';
            if (is_array($arguments)) {
                $args .= ' [';
                foreach ($arguments as $key => $value) {
                    //$args .= $key . ' => ' . $value;
                    $args .= $value;
                }
                $args .= ']';
            }
            $this->warn('<warning>"' . $command . $args . '" shall be run directly</warning>');
        } else {
            return parent::call($command, $arguments);
        }
    }

    public function get($key) {
        $key = \Cache::get($key);
        return $key;
    }
    
    public function put($key, $value, $seconds = 120) {
        return \Cache::put($key, $value, $seconds);
    }
}