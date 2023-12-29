<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-extension/auth.
 *
 * @link     https://github.com/hyperf-extension/auth
 * @contact  admin@ilover.me
 * @license  https://github.com/hyperf-extension/auth/blob/master/LICENSE
 */
namespace HyperfExtension\Auth\Access;

use Hyperf\Context\ApplicationContext;
use HyperfExtension\Auth\Contracts\Access\GateManagerInterface;
use HyperfExtension\Auth\Contracts\AuthenticatableInterface;

trait AuthorizesRequests
{
    /**
     * Authorize a given action for the current user.
     *
     * @param mixed $ability
     * @param array|mixed $arguments
     * @throws \HyperfExtension\Auth\Exceptions\AuthorizationException
     * @return \HyperfExtension\Auth\Access\Response
     */
    public function authorize($ability, $arguments = [])
    {
        [$ability, $arguments] = $this->parseAbilityAndArguments($ability, $arguments);

        return ApplicationContext::getContainer()
            ->get(GateManagerInterface::class)
            ->authorize($ability, $arguments);
    }

    /**
     * Authorize a given action for a user.
     *
     * @param mixed $ability
     * @param array|mixed $arguments
     * @throws \HyperfExtension\Auth\Exceptions\AuthorizationException
     * @return \HyperfExtension\Auth\Access\Response
     */
    public function authorizeForUser(AuthenticatableInterface $user, $ability, $arguments = [])
    {
        [$ability, $arguments] = $this->parseAbilityAndArguments($ability, $arguments);

        return ApplicationContext::getContainer()
            ->get(GateManagerInterface::class)
            ->forUser($user)
            ->authorize($ability, $arguments);
    }

    /**
     * Guesses the ability's name if it wasn't provided.
     *
     * @param mixed $ability
     * @param array|mixed $arguments
     */
    protected function parseAbilityAndArguments($ability, $arguments): array
    {
        if (is_string($ability) && strpos($ability, '\\') === false) {
            return [$ability, $arguments];
        }

        $method = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['function'];

        return [$this->normalizeGuessedAbilityName($method), $ability];
    }

    /**
     * Normalize the ability name that has been guessed from the method name.
     */
    protected function normalizeGuessedAbilityName(string $ability): string
    {
        $map = $this->resourceAbilityMap();

        return $map[$ability] ?? $ability;
    }

    /**
     * Get the map of resource methods to ability names.
     */
    protected function resourceAbilityMap(): array
    {
        return [
            'index' => 'viewAny',
            'show' => 'view',
            'create' => 'create',
            'store' => 'create',
            'edit' => 'update',
            'update' => 'update',
            'destroy' => 'delete',
        ];
    }

    /**
     * Get the list of resource methods which do not have model parameters.
     */
    protected function resourceMethodsWithoutModels(): array
    {
        return ['index', 'create', 'store'];
    }
}
