<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\RegistrationForm;
use App\Services\GoogleFormSchemaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RegistrationController extends Controller
{
    public function index(GoogleFormSchemaService $schemaService): View
    {
        $form = RegistrationForm::query()
            ->ordered()
            ->first();

        $schema = ($form !== null && $form->isOpen())
            ? $schemaService->getSchema($form)
            : null;

        return view('registration.show', [
            'form' => $form,
            'schema' => $schema,
        ]);
    }

    public function submit(Request $request, GoogleFormSchemaService $schemaService): RedirectResponse
    {
        $form = RegistrationForm::query()
            ->ordered()
            ->first();

        if ($form === null || ! $form->isOpen()) {
            return redirect()
                ->route('pendaftaran.index')
                ->withErrors(['form' => 'Pendaftaran saat ini sedang ditutup.']);
        }

        $schema = $schemaService->getSchema($form);
        if ($schema === null) {
            return redirect()
                ->route('pendaftaran.index')
                ->withInput()
                ->withErrors(['form' => 'Formulir pendaftaran belum dapat dimuat. Silakan coba lagi atau isi melalui Google Form.']);
        }

        $rules = [];
        $attributes = [];

        foreach ($schema['fields'] as $field) {
            $inputName = $field['input_name'];
            $options = $field['options'] ?? [];
            $fieldRules = [$field['required'] ? 'required' : 'nullable'];

            if ($field['type'] === 'checkbox') {
                $fieldRules[] = 'array';
                $itemRules = ['string', 'max:500'];
                if ($options !== []) {
                    $itemRules[] = Rule::in($options);
                }
                $rules[$inputName.'.*'] = $itemRules;
                $attributes[$inputName.'.*'] = $field['label'];
            } elseif ($field['type'] === 'email') {
                $fieldRules[] = 'string';
                $fieldRules[] = 'email';
                $fieldRules[] = 'max:255';
            } elseif (in_array($field['type'], ['select', 'radio'], true)) {
                $fieldRules[] = 'string';
                $fieldRules[] = 'max:500';
                if ($options !== []) {
                    $fieldRules[] = Rule::in($options);
                }
            } else {
                $fieldRules[] = 'string';
                $fieldRules[] = 'max:2000';
            }

            $rules[$inputName] = $fieldRules;
            $attributes[$inputName] = $field['label'];
        }

        $validated = $request->validate($rules, [
            'required' => 'Kolom :attribute wajib diisi.',
            'email' => 'Format :attribute harus berupa alamat email yang valid.',
            'max' => 'Kolom :attribute terlalu panjang.',
            'in' => 'Pilihan :attribute tidak valid.',
        ], $attributes);

        if (! $schemaService->submit($form, $validated)) {
            return redirect()
                ->route('pendaftaran.index')
                ->withInput()
                ->withErrors(['form' => 'Gagal mengirim data pendaftaran ke server. Silakan coba beberapa saat lagi.']);
        }

        return redirect()
            ->route('pendaftaran.index')
            ->with('registration_submitted', true);
    }
}
