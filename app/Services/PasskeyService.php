<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Throwable;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\Denormalizer\WebauthnSerializerFactory;
use Webauthn\Exception\AuthenticatorResponseVerificationException;
use Webauthn\Exception\InvalidDataException;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialUserEntity;

class PasskeyService
{
    /**
     * Generates options for creating a new public key credential for a user.
     * This method prepares the necessary data to be used with the WebAuthn API
     * for creating a new public key credential for the provided user.
     * @see https://www.w3.org/TR/webauthn-2/
     *
     * @param User $user The user for whom the credential will be created.
     * @return PublicKeyCredentialCreationOptions The options for creating a new public key credential.
     *
     * @throws InvalidDataException If user data is missing or invalid.
     */
    public function createRegisterOptions(User $user): PublicKeyCredentialCreationOptions
    {
        return PublicKeyCredentialCreationOptions::create(
            rp: new PublicKeyCredentialRpEntity(
                name: Config::get('app.name'),
                id: parse_url(Config::get('app.url'), PHP_URL_HOST),
            ),
            user: new PublicKeyCredentialUserEntity(
                name: $user->email,
                id: $user->id,
                displayName: $user->userProfile->full_name,
            ),
            challenge: Str::random(32),
        );
    }

    /**
     * Generates options for authenticating a user with a public key credential.
     * This method prepares the necessary data to be used with the WebAuthn API
     * for authenticating a user with an existing public key credential.
     * @see https://www.w3.org/TR/webauthn-2/
     *
     * @return PublicKeyCredentialRequestOptions The options for authenticating a user.
     */
    public function createAuthenticateOptions(): PublicKeyCredentialRequestOptions
    {
        return PublicKeyCredentialRequestOptions::create(
            challenge: Str::random(32),
            rpId: parse_url(config('app.url'), PHP_URL_HOST),
        );
    }

    /**
     * Registers a new public key credential for a user.
     * This method validates an attestation response from the authenticator and
     * creates a `PublicKeyCredentialSource` object, which can be stored and used for
     * future authentication attempts for the user.
     * @see https://www.w3.org/TR/webauthn-2/
     *
     * @param string $requestHost The host name of the request.
     * @param PublicKeyCredentialCreationOptions $publicKeyCredentialCreationOptions
     * @param AuthenticatorAttestationResponse $attestationResponse The attestation response from the authenticator.
     * @return PublicKeyCredentialSource The created public key credential source.
     * @throws Throwable If there is an error during the validation or creation process. This should not normally throw unless someone is performing an attack.
     */
    public function registerPublicKeyCredential(
        string                             $requestHost,
        PublicKeyCredentialCreationOptions $publicKeyCredentialCreationOptions,
        AuthenticatorAttestationResponse   $attestationResponse,
    ): PublicKeyCredentialSource {
        return AuthenticatorAttestationResponseValidator::create(
            (new CeremonyStepManagerFactory())->creationCeremony(),
        )->check(
            authenticatorAttestationResponse: $attestationResponse,
            publicKeyCredentialCreationOptions: $publicKeyCredentialCreationOptions,
            host: $requestHost,
        );
    }

    /**
     * Verifies a public key credential assertion.
     * This method validates an assertion response from the authenticator and verifies
     * the user's possession of the associated public key credential.
     * @see https://www.w3.org/TR/webauthn-2/
     *
     * @param string $requestHost The hostname of the request.
     * @param PublicKeyCredentialSource $publicKeyCredentialSource The previously created public key credential source.
     * @param PublicKeyCredentialRequestOptions $publicKeyCredentialRequestOptions The options used for user authentication.
     * @param AuthenticatorAssertionResponse $assertionResponse The assertion response received from the authenticator.
     * @param string|null $userHandle (Optional) The user handle associated with the credential (if known).
     * @return PublicKeyCredentialSource The updated public key credential source.
     *
     * @throws AuthenticatorResponseVerificationException If the assertion response is invalid.
     */
    public function verifyPublicKeyCredential(
        string                            $requestHost,
        PublicKeyCredentialSource         $publicKeyCredentialSource,
        PublicKeyCredentialRequestOptions $publicKeyCredentialRequestOptions,
        AuthenticatorAssertionResponse    $assertionResponse,
        ?string                           $userHandle = null,
    ): PublicKeyCredentialSource {
        return AuthenticatorAssertionResponseValidator::create(
            (new CeremonyStepManagerFactory())->requestCeremony()
        )->check(
            publicKeyCredentialSource: $publicKeyCredentialSource,
            authenticatorAssertionResponse: $assertionResponse,
            publicKeyCredentialRequestOptions: $publicKeyCredentialRequestOptions,
            host: $requestHost,
            userHandle: $userHandle,
        );
    }

    /**
     * Serializes a given object into a JSON string.
     * This method uses the WebAuthn serializer to serialize the object into a JSON string.
     *
     * @param object $data The object to be serialized.
     * @return string The serialized JSON string.
     */
    public function serialize(object $data): string
    {
        return (new WebauthnSerializerFactory(AttestationStatementSupportManager::create()))
            ->create()
            ->serialize($data, 'json');
    }


    /**
     * Deserializes a JSON string into a specified object.
     * This method uses the WebAuthn serializer to deserialize a JSON string into an object of the specified type.
     *
     * @template TReturn
     * @param string $json The JSON string to be deserialized.
     * @param class-string<TReturn> $into The class name of the object to deserialize into.
     * @return TReturn The deserialized object.
     */
    public function deserialize(string $json, string $into)
    {
        return (new WebauthnSerializerFactory(AttestationStatementSupportManager::create()))
            ->create()
            ->deserialize($json, $into, 'json');
    }
}
