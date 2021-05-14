<?php
namespace App;

class Node_Trans extends \Twig_Extensions_Node_Trans
{

    /**
     * @param bool $plural Return plural or singular function to use
     *
     * @return string
     */
    protected function getTransFunction($plural)
    {
        return "gettext2";
        return $plural ? 'ngettext' : 'gettext';
    }
}

class TokenParser extends \Twig\Extensions\TokenParser\TransTokenParser
{
    /**
     * {@inheritdoc}
     */
    public function parse(\Twig\Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();
        $count = null;
        $plural = null;
        $notes = null;

        if (!$stream->test(\Twig\Token::BLOCK_END_TYPE)) {
            $body = $this->parser->getExpressionParser()->parseExpression();
        } else {
            $stream->expect(\Twig\Token::BLOCK_END_TYPE);
            $body = $this->parser->subparse(array($this, 'decideForFork'));
            $next = $stream->next()->getValue();

            if ('plural' === $next) {
                $count = $this->parser->getExpressionParser()->parseExpression();
                $stream->expect(\Twig\Token::BLOCK_END_TYPE);
                $plural = $this->parser->subparse(array($this, 'decideForFork'));

                if ('notes' === $stream->next()->getValue()) {
                    $stream->expect(\Twig\Token::BLOCK_END_TYPE);
                    $notes = $this->parser->subparse(array($this, 'decideForEnd'), true);
                }
            } elseif ('notes' === $next) {
                $stream->expect(\Twig\Token::BLOCK_END_TYPE);
                $notes = $this->parser->subparse(array($this, 'decideForEnd'), true);
            }
        }

        $stream->expect(\Twig\Token::BLOCK_END_TYPE);

        $this->checkTransString($body, $lineno);

        return new Node_Trans($body, $plural, $count, $notes, $lineno, $this->getTag());
    }
}

class TwigI18n extends \Twig\Extension\AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        return array(new TokenParser());
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig\TwigFilter('trans', function ($str) {
                return str_rot13($str);
            }),
        );
    }

    public function getFunctions()
    {
        return [
            new \Twig\TwigFunction('trans', function ($str) {
                return str_rot13($str);
            })
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'i18n';
    }
}
