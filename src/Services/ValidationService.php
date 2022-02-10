<?php

namespace Grafite\Cms\Services;

use Cms;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class ValidationService
{
    /**
     * Validation check.
     */
    public function check(string|array $form, bool $jsonInput = false): array
    {
        $result = [];
        $errors = [];
        $inputs = [];

        if (is_array($form)) {
            $fields = $form;
        } else {
            $conditions = Cms::config('validation.'.$form);
            $fields = $conditions;
        }

        if (! is_array($fields)) {
            $fields = [$fields];
        }

        $validationRules = $validationInputs = [];

        foreach ($fields as $key => $value) {
            if (isset($fields[$key])) {
                $inputs[$key] = $this->getInput($key, $jsonInput);
                $validationInputs[$key] = $this->getInput($key, $jsonInput);
                $validationRules[$key] = $fields[$key];
            }
        }

        $validation = Validator::make($validationInputs, $validationRules);

        if ($validation->fails()) {
            $errors = $validation->messages();
        }

        if (! $jsonInput) {
            $result['redirect'] = Redirect::back()->with('errors', collect($errors))->with('inputs', $this->inputsArray($jsonInput));
        }

        if (! empty($errors)) {
            $result['errors'] = $errors;
        } else {
            $result['errors'] = false;
        }

        $result['inputs'] = $this->inputsArray($jsonInput);

        return $result;
    }

    /**
     * ValidationService Errors.
     */
    public function errors(string $format = 'array'): mixed
    {
        $errorMessage = '';
        $errors = Session::get('errors') ?: false;

        if (! $errors) {
            return false;
        }

        if ($format === 'string') {
            foreach ($errors as $error => $message) {
                $errorMessage .= $message.'<br>';
            }
        } else {
            $errorMessage = Session::get('errors');
        }

        return $errorMessage;
    }

    /**
     * Get input.
     */
    private function getInput(string $key, bool $jsonInput): mixed
    {
        if ($jsonInput) {
            return Request::json($key);
        } elseif (Request::file($key)) {
            return Request::file($key);
        }

        return Request::get($key);
    }

    /**
     * Get the inputs as an array.
     */
    private function inputsArray(bool $jsonInput): array
    {
        if ($jsonInput) {
            return Request::json();
        }

        $inputs = Request::all();

        // Don't send the token back
        unset($inputs['_token']);

        foreach ($inputs as $key => $value) {
            if (Request::file($key)) {
                unset($inputs[$key]);
            }
        }

        return $inputs;
    }

    /**
     * Get the value last attempted in valuation.
     */
    public function value(string $key): string
    {
        $inputs = Session::get('inputs') ?: false;

        if (! $inputs) {
            return '';
        }

        return $inputs[$key];
    }
}
