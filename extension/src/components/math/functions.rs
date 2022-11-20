use ext_php_rs::args::Arg;
use ext_php_rs::builders::FunctionBuilder;
use ext_php_rs::convert::IntoZval;
use ext_php_rs::flags::DataType;
use ext_php_rs::prelude::ModuleBuilder;
use ext_php_rs::types::Zval;
use ext_php_rs::zend::ExecuteData;

pub fn register(module: ModuleBuilder) -> ModuleBuilder {
    module
        .function(
            FunctionBuilder::new("Psl\\Math\\to_base", to_base)
                .arg(Arg::new("number", DataType::Long))
                .arg(Arg::new("base", DataType::Long))
                .returns(DataType::String, false, false)
                .build()
                .unwrap(),
        )
        .function(
            FunctionBuilder::new("Psl\\Math\\from_base", from_base)
                .arg(Arg::new("number", DataType::String))
                .arg(Arg::new("base", DataType::Long))
                .returns(DataType::Long, false, false)
                .build()
                .unwrap(),
        )
}

#[doc(hidden)]
pub extern "C" fn to_base(ex: &mut ExecuteData, retval: &mut Zval) {
    let mut number_arg = Arg::new("number", DataType::Long);
    let mut base_arg = Arg::new("base", DataType::Long);
    if ex
        .parser()
        .arg(&mut number_arg)
        .arg(&mut base_arg)
        .parse()
        .is_err()
    {
        return;
    }

    let mut number: i64 = parse_argument!(number_arg, "number");
    let base: i64 = parse_argument!(base_arg, "base");

    let mut result = vec![];

    loop {
        let n = number % base;
        number /= base;

        // panics if `base` is < 2 or > 32, PSL php implementation
        // also fails, and considers passing invalid value
        // an undefined behavior, so panicing is not really an
        // issue, respect the function signature.
        result.push(char::from_digit(n.try_into().unwrap(), base.try_into().unwrap()).unwrap());
        if number == 0 {
            break;
        }
    }

    let value = result.into_iter().rev().collect::<String>();

    set_return!(value, retval);
}

#[doc(hidden)]
pub extern "C" fn from_base(ex: &mut ExecuteData, retval: &mut Zval) {
    let mut number_arg = Arg::new("number", DataType::String);
    let mut base_arg = Arg::new("base", DataType::Long);
    if ex
        .parser()
        .arg(&mut number_arg)
        .arg(&mut base_arg)
        .parse()
        .is_err()
    {
        return;
    }

    let number: &str = parse_argument!(number_arg, "number");
    let base: i64 = parse_argument!(base_arg, "base");

    let value = i64::from_str_radix(number, base.try_into().unwrap()).unwrap();

    set_return!(value, retval);
}
