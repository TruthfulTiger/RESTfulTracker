<?php
//https://github.com/xfra35/f3-access
class Access extends \Prefab {

    //Constants
    const
        DENY='deny',
        ALLOW='allow';

    /** @var string Default policy */
    protected $policy=self::ALLOW;

    /** @var array */
    protected array $rules=array();

    /**
     * Define an access rule to a route
     * @param bool $accept
     * @param string $route
     * @param array|string $subjects
     * @return self
     */
    function rule(bool $accept, string $route, array|string $subjects=''): static
    {
        if (!is_array($subjects))
            $subjects=explode(',',$subjects);
        list($verbs,$path)=$this->parseRoute($route);
        foreach($subjects as $subject)
            foreach($verbs as $verb)
                $this->rules[$subject?:'*'][$verb][$path]=$accept;
        return $this;
    }

    /**
     * Give access to a route
     * @param string $route
     * @param array|string $subjects
     * @return self
     */
    function allow(string $route, array|string $subjects=''): Access|static
    {
        return $this->rule(TRUE,$route,$subjects);
    }

    /**
     * Deny access to a route
     * @param string $route
     * @param array|string $subjects
     * @return self
     */
    function deny(string $route, array|string $subjects=''): Access|static
    {
        return $this->rule(FALSE,$route,$subjects);
    }

    /**
     * Get/set the default policy
     * @param string|null $default
     * @return self|string
     */
    function policy(string $default=NULL): string|static
    {
        if (!isset($default))
            return $this->policy;
        if (in_array($default=strtolower($default),array(self::ALLOW,self::DENY)))
            $this->policy=$default;
        return $this;
    }

    /**
     * Return TRUE if the given subject (or any of the given subjects) is granted access to the given route
     * @param array|string $route
     * @param array|string $subject
     * @return bool
     */
    function granted(array|string $route, array|string $subject=''): bool
    {
        list($verbs,$uri)=is_array($route)?$route:$this->parseRoute($route);
        if (is_array($subject)) {
            foreach($subject?:array('') as $s)
                if ($this->granted([$verbs,$uri],$s))
                    return TRUE;
            return FALSE;
        }
        $verb=$verbs[0];//we shouldn't get more than one verb here
        $specific= $this->rules[$subject][$verb] ?? array();
        $global= $this->rules['*'][$verb] ?? array();
        $rules=$specific+$global;//subject-specific rules have precedence over global rules
        //specific paths are processed first:
        $paths=array();
        foreach ($keys=array_keys($rules) as $key) {
            $path=str_replace('@','*@',$key);
            if (!str_ends_with($path, '*'))
                $path.='+';
            $paths[]=$path;
        }
        $vals=array_values($rules);
        array_multisort($paths,SORT_DESC,$keys,$vals);
        $rules=array_combine($keys,$vals);
        foreach($rules as $path=>$rule)
            if (preg_match('/^'.preg_replace('/@\w*/','[^\/]+',str_replace('\*','.*',preg_quote($path,'/'))).'$/',$uri))
                return $rule;
        return $this->policy==self::ALLOW;
    }

    /**
     * Authorize a given subject (or a set of subjects)
     * @param array|string $subject
     * @param callable|string|null $ondeny
     * @return bool
     */
    function authorize(array|string $subject='', callable|string $ondeny=NULL): bool
    {
        $f3=\Base::instance();
        if (!$this->granted($route=$f3->VERB.' '.$f3->PATH,$subject) &&
            (!isset($ondeny) || FALSE===$f3->call($ondeny,array($route,$subject)))) {
            $f3->error($subject?403:401);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Parse a route string
     * Possible route formats are:
     * - GET /foo
     * - GET|PUT /foo
     * - /foo
     * - * /foo
     * @param $str
     * @return array
     */
    protected function parseRoute($str): array
    {
        $verbs=$path='';
        if (preg_match('/^\h*(\*|[\|\w]*)\h*(\H+)/',$str,$m)) {
            list(,$verbs,$path)=$m;
            if ($path[0]=='@')
                $path=\Base::instance()->get('ALIASES.'.substr($path,1));
        }
        if (!$verbs || $verbs=='*')
            $verbs=\Base::VERBS;
        return array(explode('|',$verbs),$path);
    }

    /**
     * Constructor
     * @param array|null $config
     */
    function __construct(array $config=NULL) {
        if (!isset($config)) {
            $f3=\Base::instance();
            $config=(array)$f3->get('ACCESS');
        }
        if (isset($config['policy']))
            $this->policy($config['policy']);
        if (isset($config['rules']))
            foreach((array)$config['rules'] as $str=>$subjects) {
                foreach(array(self::DENY,self::ALLOW) as $k=>$policy)
                    if (stripos($str,$policy)===0)
                        $this->rule((bool)$k,substr($str,strlen($policy)),$subjects);
            }
    }

}
