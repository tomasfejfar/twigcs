<?php

namespace Allocine\TwigLinter\Rule;

use Allocine\TwigLinter\Lexer;

class UnusedMacro extends AbstractRule implements RuleInterface
{
    /**
     * {@inheritdoc}
     */
    public function check(\Twig_TokenStream $tokens)
    {
        $this->reset();

        $macros = [];

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if ($token->getType() === \Twig_Token::NAME_TYPE && $token->getValue() === 'import') {
                while ($tokens->getCurrent()->getValue() !== 'as') {
                    $tokens->next();
                }

                $tokens->next();

                while (in_array($tokens->getCurrent()->getType(), [\Twig_Token::NAME_TYPE, \Twig_Token::PUNCTUATION_TYPE, Lexer::WHITESPACE_TYPE])) {
                    $next = $tokens->getCurrent();
                    if ($next->getType() === \Twig_Token::NAME_TYPE) {
                        $macros[$next->getValue()] = $next->getLine();
                    }

                    $tokens->next();
                }
            } elseif ($token->getType() === \Twig_Token::NAME_TYPE && array_key_exists($token->getValue(), $macros)) {
                unset($macros[$token->getValue()]);
            }

            $tokens->next();
        }


        foreach ($macros as $name => $line) {
            $this->addViolation(
                $tokens->getFilename(),
                $token->getLine(),
                sprintf('Unused macro "%s".', $name)
            );
        }

        return $this->violations;
    }
}
