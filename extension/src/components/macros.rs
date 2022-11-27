#![macro_use]

#[macro_export]
macro_rules! implement_class {
    ($class:ty, $class_name:ident) => {
        impl<'a> ext_php_rs::convert::FromZendObject<'a> for &'a $class {
            #[inline]
            fn from_zend_object(
                obj: &'a ext_php_rs::types::ZendObject,
            ) -> ext_php_rs::error::Result<Self> {
                let obj = ext_php_rs::types::ZendClassObject::<$class>::from_zend_obj(obj)
                    .ok_or(ext_php_rs::error::Error::InvalidScope)?;

                Ok(&**obj)
            }
        }

        impl<'a> ext_php_rs::convert::FromZendObjectMut<'a> for &'a mut $class {
            #[inline]
            fn from_zend_object_mut(
                obj: &'a mut ext_php_rs::types::ZendObject,
            ) -> ext_php_rs::error::Result<Self> {
                let obj = ext_php_rs::types::ZendClassObject::<$class>::from_zend_obj_mut(obj)
                    .ok_or(ext_php_rs::error::Error::InvalidScope)?;

                Ok(&mut **obj)
            }
        }

        impl<'a> ext_php_rs::convert::FromZval<'a> for &'a $class {
            const TYPE: ext_php_rs::flags::DataType =
                ext_php_rs::flags::DataType::Object(Some($class_name));

            #[inline]
            fn from_zval(zval: &'a ext_php_rs::types::Zval) -> Option<Self> {
                <Self as ext_php_rs::convert::FromZendObject>::from_zend_object(zval.object()?).ok()
            }
        }

        impl<'a> ext_php_rs::convert::FromZvalMut<'a> for &'a mut $class {
            const TYPE: ext_php_rs::flags::DataType =
                ext_php_rs::flags::DataType::Object(Some($class_name));

            #[inline]
            fn from_zval_mut(zval: &'a mut ext_php_rs::types::Zval) -> Option<Self> {
                <Self as ext_php_rs::convert::FromZendObjectMut>::from_zend_object_mut(
                    zval.object_mut()?,
                )
                .ok()
            }
        }

        impl ext_php_rs::convert::IntoZendObject for $class {
            #[inline]
            fn into_zend_object(
                self,
            ) -> ext_php_rs::error::Result<ext_php_rs::boxed::ZBox<ext_php_rs::types::ZendObject>>
            {
                Ok(ext_php_rs::types::ZendClassObject::new(self).into())
            }
        }

        impl ext_php_rs::convert::IntoZval for $class {
            const TYPE: ext_php_rs::flags::DataType =
                ext_php_rs::flags::DataType::Object(Some($class_name));

            #[inline]
            fn set_zval(
                self,
                zv: &mut ext_php_rs::types::Zval,
                persistent: bool,
            ) -> ext_php_rs::error::Result<()> {
                use ext_php_rs::convert::IntoZendObject;

                self.into_zend_object()?.set_zval(zv, persistent)
            }
        }
    };
}

#[macro_export]
macro_rules! throw_default {
    ($message:expr) => {
        ::ext_php_rs::exception::PhpException::default($message.into())
            .throw()
            .expect(&format!("Failed to throw exception: {}", $message));

        return;
    };
}

#[macro_export]
macro_rules! parse_argument {
    ($arg:expr, $name:literal) => {
        match $arg.val() {
            Some(val) => val,
            None => {
                throw_default!(format!("Invalid value given for argument `{}`.", $name));
            }
        }
    };
    ($arg:expr, $name:literal, $default:expr) => {
        match $arg.val() {
            Some(val) => val,
            None => $default,
        }
    };
}

#[macro_export]
macro_rules! parse_zval_argument {
    ($arg:expr, $name:literal) => {
        match $arg.zval() {
            Some(val) => val,
            None => {
                throw_default!(format!("Invalid value given for argument `{}`.", $name));
            }
        }
    };
}

#[macro_export]
macro_rules! set_return {
    ($result:expr, $retval:expr) => {
        if let Err(e) = $result.set_zval($retval, false) {
            let e: ::ext_php_rs::exception::PhpException = e.into();
            e.throw().expect("Failed to throw exception");
        }

        return;
    };
}

#[macro_export]
macro_rules! throw {
    ($ce:expr, $message:expr) => {
        let e: ::ext_php_rs::exception::PhpException =
            ::ext_php_rs::exception::PhpException::new($message.to_string(), 0, $ce);

        e.throw()
            .expect(&format!("Failed to throw exception: {}", $message));
    };
}

macro_rules! parse_method {
    ($ex:expr $(,$arg:expr)*) => {{
        let (parser, this) = $ex.parser_method::<Self>();
        let parser = parser$(.arg(&mut $arg))*.parse();
        if parser.is_err() {
            return;
        }

        let this = match this {
            Some(this) => this,
            None => {
                ::ext_php_rs::exception::PhpException::default("Failed to retrieve reference to `$this`".into())
                .throw()
                .expect("Failed to throw exception: Failed to retrieve reference to `$this`");

                return;
            }
        };

        this
    }};
    ($ex:expr $(,$arg:expr)*, ?= $($optional_arg:expr),*) => {{
        let (parser, this) = $ex.parser_method::<Self>();
        let parser = parser$(.arg(&mut $arg))*.not_required()$(.arg(&mut $optional_arg))*.parse();
        if parser.is_err() {
            return;
        }

        let this = match this {
            Some(this) => this,
            None => {
                ::ext_php_rs::exception::PhpException::default("Failed to retrieve reference to `$this`".into())
                .throw()
                .expect("Failed to throw exception: Failed to retrieve reference to `$this`");

                return;
            }
        };

        this
    }};
}
