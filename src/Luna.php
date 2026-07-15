<?php

namespace LaraGram\Luna;

use LaraGram\Support\Facades\Facade;

/**
 * @method static void setRootView(string $name)
 * @method static void share(string|array<array-key, mixed>|\LaraGram\Contracts\Support\Arrayable<array-key, mixed>|\LaraGram\Luna\ProvidesLunaProperties $key, mixed $value = null)
 * @method static mixed getShared(string|null $key = null, mixed $default = null)
 * @method static void flushShared()
 * @method static void version(\Closure|string|null $version)
 * @method static string getVersion()
 * @method static void resolveUrlUsing(\Closure|null $urlResolver = null)
 * @method static void transformComponentUsing(\Closure|null $componentTransformer = null)
 * @method static void clearHistory()
 * @method static void preserveFragment()
 * @method static void encryptHistory(bool $encrypt = true)
 * @method static void disableSsr(\Closure|bool $condition = true)
 * @method static void withoutSsr(array<int, string>|string $paths)
 * @method static \LaraGram\Luna\OptionalProp optional(callable $callback)
 * @method static \LaraGram\Luna\DeferProp defer(callable $callback, string $group = 'default', bool $rescue = false)
 * @method static \LaraGram\Luna\MergeProp merge(mixed $value)
 * @method static \LaraGram\Luna\MergeProp deepMerge(mixed $value)
 * @method static \LaraGram\Luna\AlwaysProp always(mixed $value)
 * @method static \LaraGram\Luna\ScrollProp<mixed> scroll(mixed $value, string $wrapper = 'data', \LaraGram\Luna\ProvidesScrollMetadata|callable|null $metadata = null)
 * @method static \LaraGram\Luna\OnceProp once(callable $value)
 * @method static \LaraGram\Luna\OnceProp shareOnce(string $key, callable $callback)
 * @method static \LaraGram\Luna\Response render(\BackedEnum|\UnitEnum|string $component, array<array-key, mixed>|\LaraGram\Contracts\Support\Arrayable<array-key, mixed>|\LaraGram\Luna\ProvidesLunaProperties $props = [])
 * @method static \LaraGram\Http\BaseResponse location(string|\LaraGram\Http\BaseRedirectResponse $url)
 * @method static void handleExceptionsUsing(callable $callback)
 * @method static \LaraGram\Luna\ResponseFactory flash(\BackedEnum|\UnitEnum|string|array<string, mixed> $key, mixed $value = null)
 * @method static \LaraGram\Http\BaseRedirectResponse back(int $status = 302, array<string, string> $headers = [], mixed $fallback = false)
 * @method static mixed answerQuery(array<array-key, mixed>|object $result, string|null $queryId = null)
 * @method static mixed shareMessage(array<array-key, mixed>|object $result, int|null $userId = null, array<string, bool> $options = [])
 * @method static mixed invoiceLink(array<string, mixed> $params)
 * @method static mixed setMenuButton(array<array-key, mixed>|object $menuButton, int|null $chatId = null)
 * @method static mixed miniAppMenuButton(string $text, string $url, int|null $chatId = null)
 * @method static array<string, mixed> getFlashed(\LaraGram\Http\Request|null $request = null)
 * @method static array<string, mixed> pullFlashed(\LaraGram\Http\Request|null $request = null)
 * @method static void macro(string $name, object|callable $macro)
 * @method static void mixin(object $mixin, bool $replace = true)
 * @method static bool hasMacro(string $name)
 * @method static void flushMacros()
 *
 * @see \LaraGram\Luna\ResponseFactory
 */
class Luna extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ResponseFactory::class;
    }
}
