<?php

const PHEASEL_ENVIRONMENT_PROD = 'prod';
const PHEASEL_ENVIRONMENT_DEV = 'dev';
/**
 * Whether the current instance of PHeasel is running in a development or production environment. In PROD mode,
 * the PHeasel developer bar is not displayed and markup file cache auto update is deactivated (regardless of
 * PHEASEL_AUTO_UPDATE_FILES_CACHE setting.
 */
const PHEASEL_ENVIRONMENT = PHEASEL_ENVIRONMENT_DEV;



/**
 * Automatically refresh internal file cache with every request. Although doing so is handy when getting started with
 * a new website, it might cause major performance issues on a live server, or when working locally with a large
 * number of templates, pages and snippets. It is recommended to disable auto-update as soon as a basic set of
 * templates, pages and snippets is finished and instead adding a bookmark pointing to
 * http://localhost:8080/pheasel/update-file-cache.php
 * in order to easily update the cache manually..
 */
const PHEASEL_AUTO_UPDATE_FILES_CACHE = true;

/**
 * Patterns for PHeasel placeholders, may be changed to avoid eventual conflicts with other tools processing your markup.
 * When changing these values, be aware that any of the prefixes and suffixes must be unambiguous, e.g. %whatever%
 * won't work, same goes for $%whatever% (in both cases, searching for % would match suffix *and* prefix.
 */
const PHEASEL_PLACEHOLDER_PREFIX = '${';
const PHEASEL_PLACEHOLDER_SUFFIX = '}$';

/**
 * Escape placeholder prefix and suffix in order to avoid PHeasel processing them. Mainly useful if you actually
 * want to output PHeasel example code (or other code that PHeasel might mistake for it's placeholders.
 */
const PHEASEL_PLACEHOLDER_PREFIX_ESCAPED = '$\'{';
const PHEASEL_PLACEHOLDER_SUFFIX_ESCAPED = '}\'$';

/**
 * Regular expression to match find placeholder strings. The 1st group (in brackets) matches only
 * the content of the placeholder, omitting the ${ and }$. The 's' modifier adds support for multiline placeholders
 */
const PHEASEL_PLACEHOLDER_REGEX = '/\$\{(.+?)\}\$/s';
