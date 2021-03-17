# Documentation

This documentation contains a list of the functions, interfaces and classes this library provides.

Please click through to read the docblock comment details for each of them.

---


### `Psl`

#### `Functions`

- [invariant](./../src/Psl/invariant.php#L18)
- [invariant_violation](./../src/Psl/invariant_violation.php#L16)
- [sequence](./../src/Psl/sequence.php#L16)


### `Psl\Arr`

#### `Functions`

- [at](./../src/Psl/Arr/at.php#L27) ( deprecated )
- [concat](./../src/Psl/Arr/concat.php#L22) ( deprecated )
- [contains](./../src/Psl/Arr/contains.php#L24) ( deprecated )
- [contains_key](./../src/Psl/Arr/contains_key.php#L25) ( deprecated )
- [count](./../src/Psl/Arr/count.php#L36) ( deprecated )
- [count_values](./../src/Psl/Arr/count_values.php#L22) ( deprecated )
- [equal](./../src/Psl/Arr/equal.php#L22) ( deprecated )
- [fill](./../src/Psl/Arr/fill.php#L25) ( deprecated )
- [first](./../src/Psl/Arr/first.php#L24) ( deprecated )
- [first_key](./../src/Psl/Arr/first_key.php#L25) ( deprecated )
- [firstx](./../src/Psl/Arr/firstx.php#L28) ( deprecated )
- [flatten](./../src/Psl/Arr/flatten.php#L33) ( deprecated )
- [flat_map](./../src/Psl/Arr/flat_map.php#L22) ( deprecated )
- [flip](./../src/Psl/Arr/flip.php#L28) ( deprecated )
- [group_by](./../src/Psl/Arr/group_by.php#L43) ( deprecated )
- [idx](./../src/Psl/Arr/idx.php#L35) ( deprecated )
- [keys](./../src/Psl/Arr/keys.php#L22) ( deprecated )
- [last](./../src/Psl/Arr/last.php#L24) ( deprecated )
- [last_key](./../src/Psl/Arr/last_key.php#L26) ( deprecated )
- [lastx](./../src/Psl/Arr/lastx.php#L28) ( deprecated )
- [merge](./../src/Psl/Arr/merge.php#L32) ( deprecated )
- [partition](./../src/Psl/Arr/partition.php#L22) ( deprecated )
- [random](./../src/Psl/Arr/random.php#L25) ( deprecated )
- [select_keys](./../src/Psl/Arr/select_keys.php#L25) ( deprecated )
- [shuffle](./../src/Psl/Arr/shuffle.php#L30) ( deprecated )
- [sort](./../src/Psl/Arr/sort.php#L24) ( deprecated )
- [sort_by](./../src/Psl/Arr/sort_by.php#L27) ( deprecated )
- [sort_by_key](./../src/Psl/Arr/sort_by_key.php#L26) ( deprecated )
- [sort_with_keys](./../src/Psl/Arr/sort_with_keys.php#L25) ( deprecated )
- [sort_with_keys_by](./../src/Psl/Arr/sort_with_keys_by.php#L29) ( deprecated )
- [unique](./../src/Psl/Arr/unique.php#L22) ( deprecated )
- [unique_by](./../src/Psl/Arr/unique_by.php#L26) ( deprecated )
- [values](./../src/Psl/Arr/values.php#L25) ( deprecated )
- [drop](./../src/Psl/Arr/drop.php#L31) ( deprecated )
- [drop_while](./../src/Psl/Arr/drop_while.php#L31) ( deprecated )
- [slice](./../src/Psl/Arr/slice.php#L35) ( deprecated )
- [take](./../src/Psl/Arr/take.php#L25) ( deprecated )
- [take_while](./../src/Psl/Arr/take_while.php#L31) ( deprecated )
- [filter](./../src/Psl/Arr/filter.php#L34) ( deprecated )
- [filter_keys](./../src/Psl/Arr/filter_keys.php#L34) ( deprecated )
- [filter_nulls](./../src/Psl/Arr/filter_nulls.php#L25) ( deprecated )
- [filter_with_key](./../src/Psl/Arr/filter_with_key.php#L37) ( deprecated )
- [map](./../src/Psl/Arr/map.php#L34) ( deprecated )
- [map_keys](./../src/Psl/Arr/map_keys.php#L34) ( deprecated )
- [map_with_key](./../src/Psl/Arr/map_with_key.php#L34) ( deprecated )


### `Psl\Collection`

#### `Interfaces`

- [CollectionInterface](./../src/Psl/Collection/CollectionInterface.php#L21)
- [IndexAccessInterface](./../src/Psl/Collection/IndexAccessInterface.php#L13)
- [MutableCollectionInterface](./../src/Psl/Collection/MutableCollectionInterface.php#L20)
- [MutableIndexAccessInterface](./../src/Psl/Collection/MutableIndexAccessInterface.php#L16)
- [AccessibleCollectionInterface](./../src/Psl/Collection/AccessibleCollectionInterface.php#L18)
- [MutableAccessibleCollectionInterface](./../src/Psl/Collection/MutableAccessibleCollectionInterface.php#L20)
- [VectorInterface](./../src/Psl/Collection/VectorInterface.php#L12)
- [MutableVectorInterface](./../src/Psl/Collection/MutableVectorInterface.php#L13)
- [MapInterface](./../src/Psl/Collection/MapInterface.php#L13)
- [MutableMapInterface](./../src/Psl/Collection/MutableMapInterface.php#L14)

#### `Classes`

- [Vector](./../src/Psl/Collection/Vector.php#L17)
- [MutableVector](./../src/Psl/Collection/MutableVector.php#L17)
- [Map](./../src/Psl/Collection/Map.php#L20)
- [MutableMap](./../src/Psl/Collection/MutableMap.php#L18)


### `Psl\DataStructure`

#### `Interfaces`

- [PriorityQueueInterface](./../src/Psl/DataStructure/PriorityQueueInterface.php#L12)
- [QueueInterface](./../src/Psl/DataStructure/QueueInterface.php#L17)
- [StackInterface](./../src/Psl/DataStructure/StackInterface.php#L17)

#### `Classes`

- [PriorityQueue](./../src/Psl/DataStructure/PriorityQueue.php#L18)
- [Queue](./../src/Psl/DataStructure/Queue.php#L19)
- [Stack](./../src/Psl/DataStructure/Stack.php#L19)


### `Psl\Dict`

#### `Functions`

- [associate](./../src/Psl/Dict/associate.php#L25)
- [count_values](./../src/Psl/Dict/count_values.php#L20)
- [drop](./../src/Psl/Dict/drop.php#L27)
- [drop_while](./../src/Psl/Dict/drop_while.php#L26)
- [equal](./../src/Psl/Dict/equal.php#L19)
- [filter](./../src/Psl/Dict/filter.php#L31)
- [filter_nulls](./../src/Psl/Dict/filter_nulls.php#L21)
- [filter_keys](./../src/Psl/Dict/filter_keys.php#L31)
- [filter_with_key](./../src/Psl/Dict/filter_with_key.php#L34)
- [flatten](./../src/Psl/Dict/flatten.php#L28)
- [flip](./../src/Psl/Dict/flip.php#L27)
- [from_entries](./../src/Psl/Dict/from_entries.php#L18)
- [from_iterable](./../src/Psl/Dict/from_iterable.php#L17)
- [from_keys](./../src/Psl/Dict/from_keys.php#L19)
- [group_by](./../src/Psl/Dict/group_by.php#L41)
- [map](./../src/Psl/Dict/map.php#L29)
- [map_keys](./../src/Psl/Dict/map_keys.php#L29)
- [map_with_key](./../src/Psl/Dict/map_with_key.php#L29)
- [merge](./../src/Psl/Dict/merge.php#L19)
- [partition](./../src/Psl/Dict/partition.php#L19)
- [partition_with_key](./../src/Psl/Dict/partition_with_key.php#L19)
- [pull](./../src/Psl/Dict/pull.php#L35)
- [pull_with_key](./../src/Psl/Dict/pull_with_key.php#L35)
- [reindex](./../src/Psl/Dict/reindex.php#L37)
- [select_keys](./../src/Psl/Dict/select_keys.php#L23)
- [slice](./../src/Psl/Dict/slice.php#L31)
- [sort](./../src/Psl/Dict/sort.php#L24)
- [sort_by](./../src/Psl/Dict/sort_by.php#L24)
- [sort_by_key](./../src/Psl/Dict/sort_by_key.php#L24)
- [take](./../src/Psl/Dict/take.php#L22)
- [take_while](./../src/Psl/Dict/take_while.php#L26)
- [unique](./../src/Psl/Dict/unique.php#L17)
- [unique_by](./../src/Psl/Dict/unique_by.php#L23)
- [diff](./../src/Psl/Dict/diff.php#L24)
- [diff_by_key](./../src/Psl/Dict/diff_by_key.php#L24)
- [intersect](./../src/Psl/Dict/intersect.php#L24)
- [intersect_by_key](./../src/Psl/Dict/intersect_by_key.php#L24)


### `Psl\Encoding\Base64`

#### `Functions`

- [encode](./../src/Psl/Encoding/Base64/encode.php#L18)
- [decode](./../src/Psl/Encoding/Base64/decode.php#L27)


### `Psl\Encoding\Hex`

#### `Functions`

- [encode](./../src/Psl/Encoding/Hex/encode.php#L16)
- [decode](./../src/Psl/Encoding/Hex/decode.php#L22)


### `Psl\Env`

#### `Functions`

- [args](./../src/Psl/Env/args.php#L12)
- [current_dir](./../src/Psl/Env/current_dir.php#L16)
- [current_exec](./../src/Psl/Env/current_exec.php#L18)
- [get_var](./../src/Psl/Env/get_var.php#L18)
- [get_vars](./../src/Psl/Env/get_vars.php#L14)
- [join_paths](./../src/Psl/Env/join_paths.php#L16)
- [remove_var](./../src/Psl/Env/remove_var.php#L18)
- [set_current_dir](./../src/Psl/Env/set_current_dir.php#L16)
- [set_var](./../src/Psl/Env/set_var.php#L19)
- [split_paths](./../src/Psl/Env/split_paths.php#L16)
- [temp_dir](./../src/Psl/Env/temp_dir.php#L20)


### `Psl\Filesystem`

#### `Constants`

- [SEPARATOR](./../src/Psl/Filesystem/constants.php#L0)

#### `Functions`

- [change_group](./../src/Psl/Filesystem/change_group.php#L20)
- [change_owner](./../src/Psl/Filesystem/change_owner.php#L20)
- [change_permissions](./../src/Psl/Filesystem/change_permissions.php#L19)
- [copy](./../src/Psl/Filesystem/copy.php#L21)
- [create_directory](./../src/Psl/Filesystem/create_directory.php#L17)
- [create_file](./../src/Psl/Filesystem/create_file.php#L23)
- [delete_directory](./../src/Psl/Filesystem/delete_directory.php#L23)
- [delete_file](./../src/Psl/Filesystem/delete_file.php#L20)
- [exists](./../src/Psl/Filesystem/exists.php#L19)
- [file_size](./../src/Psl/Filesystem/file_size.php#L17)
- [get_group](./../src/Psl/Filesystem/get_group.php#L18)
- [get_owner](./../src/Psl/Filesystem/get_owner.php#L18)
- [get_permissions](./../src/Psl/Filesystem/get_permissions.php#L18)
- [get_basename](./../src/Psl/Filesystem/get_basename.php#L23)
- [get_directory](./../src/Psl/Filesystem/get_directory.php#L27)
- [get_extension](./../src/Psl/Filesystem/get_extension.php#L16)
- [get_filename](./../src/Psl/Filesystem/get_filename.php#L18)
- [is_directory](./../src/Psl/Filesystem/is_directory.php#L22)
- [is_file](./../src/Psl/Filesystem/is_file.php#L22)
- [is_symbolic_link](./../src/Psl/Filesystem/is_symbolic_link.php#L19)
- [is_readable](./../src/Psl/Filesystem/is_readable.php#L20)
- [is_writable](./../src/Psl/Filesystem/is_writable.php#L20)
- [canonicalize](./../src/Psl/Filesystem/canonicalize.php#L15)
- [is_executable](./../src/Psl/Filesystem/is_executable.php#L20)
- [read_directory](./../src/Psl/Filesystem/read_directory.php#L19)
- [read_file](./../src/Psl/Filesystem/read_file.php#L24)
- [read_symbolic_link](./../src/Psl/Filesystem/read_symbolic_link.php#L21)
- [append_file](./../src/Psl/Filesystem/append_file.php#L18)
- [write_file](./../src/Psl/Filesystem/write_file.php#L18)
- [create_temporary_file](./../src/Psl/Filesystem/create_temporary_file.php#L26)
- [create_hard_link](./../src/Psl/Filesystem/create_hard_link.php#L21)
- [create_symbolic_link](./../src/Psl/Filesystem/create_symbolic_link.php#L21)
- [get_access_time](./../src/Psl/Filesystem/get_access_time.php#L18)
- [get_change_time](./../src/Psl/Filesystem/get_change_time.php#L19)
- [get_modification_time](./../src/Psl/Filesystem/get_modification_time.php#L19)
- [get_inode](./../src/Psl/Filesystem/get_inode.php#L18)


### `Psl\Fun`

#### `Functions`

- [after](./../src/Psl/Fun/after.php#L37)
- [identity](./../src/Psl/Fun/identity.php#L17)
- [pipe](./../src/Psl/Fun/pipe.php#L34)
- [rethrow](./../src/Psl/Fun/rethrow.php#L17)
- [when](./../src/Psl/Fun/when.php#L33)


### `Psl\Hash`

#### `Functions`

- [hash](./../src/Psl/Hash/hash.php#L16)
- [algorithms](./../src/Psl/Hash/algorithms.php#L16)
- [equals](./../src/Psl/Hash/equals.php#L14)

#### `Classes`

- [Context](./../src/Psl/Hash/Context.php#L31)


### `Psl\Html`

#### `Functions`

- [encode](./../src/Psl/Html/encode.php#L27)
- [encode_special_characters](./../src/Psl/Html/encode_special_characters.php#L29)
- [decode](./../src/Psl/Html/decode.php#L23)
- [decode_special_characters](./../src/Psl/Html/decode_special_characters.php#L18)
- [strip_tags](./../src/Psl/Html/strip_tags.php#L16)


### `Psl\Iter`

#### `Functions`

- [all](./../src/Psl/Iter/all.php#L28)
- [any](./../src/Psl/Iter/any.php#L28)
- [apply](./../src/Psl/Iter/apply.php#L27)
- [chain](./../src/Psl/Iter/chain.php#L31) ( deprecated )
- [chunk](./../src/Psl/Iter/chunk.php#L33) ( deprecated )
- [chunk_with_keys](./../src/Psl/Iter/chunk_with_keys.php#L30) ( deprecated )
- [contains](./../src/Psl/Iter/contains.php#L27)
- [contains_key](./../src/Psl/Iter/contains_key.php#L16)
- [count](./../src/Psl/Iter/count.php#L29)
- [diff_by_key](./../src/Psl/Iter/diff_by_key.php#L23) ( deprecated )
- [drop](./../src/Psl/Iter/drop.php#L31) ( deprecated )
- [drop_while](./../src/Psl/Iter/drop_while.php#L31) ( deprecated )
- [enumerate](./../src/Psl/Iter/enumerate.php#L23) ( deprecated )
- [filter](./../src/Psl/Iter/filter.php#L34) ( deprecated )
- [filter_keys](./../src/Psl/Iter/filter_keys.php#L36) ( deprecated )
- [filter_nulls](./../src/Psl/Iter/filter_nulls.php#L26) ( deprecated )
- [filter_with_key](./../src/Psl/Iter/filter_with_key.php#L39) ( deprecated )
- [first](./../src/Psl/Iter/first.php#L27)
- [first_key](./../src/Psl/Iter/first_key.php#L30)
- [flat_map](./../src/Psl/Iter/flat_map.php#L22) ( deprecated )
- [flatten](./../src/Psl/Iter/flatten.php#L24) ( deprecated )
- [flip](./../src/Psl/Iter/flip.php#L27) ( deprecated )
- [from_entries](./../src/Psl/Iter/from_entries.php#L24) ( deprecated )
- [from_keys](./../src/Psl/Iter/from_keys.php#L25) ( deprecated )
- [is_empty](./../src/Psl/Iter/is_empty.php#L12)
- [keys](./../src/Psl/Iter/keys.php#L28) ( deprecated )
- [last](./../src/Psl/Iter/last.php#L17)
- [last_key](./../src/Psl/Iter/last_key.php#L17)
- [map](./../src/Psl/Iter/map.php#L35) ( deprecated )
- [map_keys](./../src/Psl/Iter/map_keys.php#L35) ( deprecated )
- [map_with_key](./../src/Psl/Iter/map_with_key.php#L33) ( deprecated )
- [merge](./../src/Psl/Iter/merge.php#L30) ( deprecated )
- [product](./../src/Psl/Iter/product.php#L34) ( deprecated )
- [pull](./../src/Psl/Iter/pull.php#L40) ( deprecated )
- [pull_with_key](./../src/Psl/Iter/pull_with_key.php#L41) ( deprecated )
- [random](./../src/Psl/Iter/random.php#L23)
- [range](./../src/Psl/Iter/range.php#L45) ( deprecated )
- [reduce](./../src/Psl/Iter/reduce.php#L32)
- [reduce_keys](./../src/Psl/Iter/reduce_keys.php#L33)
- [reduce_with_keys](./../src/Psl/Iter/reduce_with_keys.php#L40)
- [reductions](./../src/Psl/Iter/reductions.php#L33) ( deprecated )
- [reindex](./../src/Psl/Iter/reindex.php#L43) ( deprecated )
- [repeat](./../src/Psl/Iter/repeat.php#L36) ( deprecated )
- [reproduce](./../src/Psl/Iter/reproduce.php#L33) ( deprecated )
- [reverse](./../src/Psl/Iter/reverse.php#L26) ( deprecated )
- [rewindable](./../src/Psl/Iter/rewindable.php#L20)
- [search](./../src/Psl/Iter/search.php#L26)
- [slice](./../src/Psl/Iter/slice.php#L36) ( deprecated )
- [take](./../src/Psl/Iter/take.php#L25) ( deprecated )
- [take_while](./../src/Psl/Iter/take_while.php#L32) ( deprecated )
- [to_array](./../src/Psl/Iter/to_array.php#L21) ( deprecated )
- [to_array_with_keys](./../src/Psl/Iter/to_array_with_keys.php#L22) ( deprecated )
- [to_iterator](./../src/Psl/Iter/to_iterator.php#L19)
- [values](./../src/Psl/Iter/values.php#L32) ( deprecated )
- [zip](./../src/Psl/Iter/zip.php#L38) ( deprecated )

#### `Classes`

- [Iterator](./../src/Psl/Iter/Iterator.php#L18)


### `Psl\Json`

#### `Functions`

- [encode](./../src/Psl/Json/encode.php#L27)
- [decode](./../src/Psl/Json/decode.php#L24)
- [typed](./../src/Psl/Json/typed.php#L22)


### `Psl\Math`

#### `Constants`

- [INT64_MAX](./../src/Psl/Math/constants.php#L0)
- [INT64_MIN](./../src/Psl/Math/constants.php#L0)
- [INT53_MAX](./../src/Psl/Math/constants.php#L0)
- [INT53_MIN](./../src/Psl/Math/constants.php#L0)
- [INT32_MAX](./../src/Psl/Math/constants.php#L0)
- [INT32_MIN](./../src/Psl/Math/constants.php#L0)
- [INT16_MAX](./../src/Psl/Math/constants.php#L0)
- [INT16_MIN](./../src/Psl/Math/constants.php#L0)
- [UINT32_MAX](./../src/Psl/Math/constants.php#L0)
- [UINT16_MAX](./../src/Psl/Math/constants.php#L0)
- [PI](./../src/Psl/Math/constants.php#L0)
- [E](./../src/Psl/Math/constants.php#L0)
- [INFINITY](./../src/Psl/Math/constants.php#L0)
- [NAN](./../src/Psl/Math/constants.php#L0)

#### `Functions`

- [abs](./../src/Psl/Math/abs.php#L34)
- [base_convert](./../src/Psl/Math/base_convert.php#L39)
- [ceil](./../src/Psl/Math/ceil.php#L25)
- [clamp](./../src/Psl/Math/clamp.php#L24)
- [cos](./../src/Psl/Math/cos.php#L22)
- [div](./../src/Psl/Math/div.php#L32)
- [exp](./../src/Psl/Math/exp.php#L22)
- [floor](./../src/Psl/Math/floor.php#L16)
- [from_base](./../src/Psl/Math/from_base.php#L27)
- [log](./../src/Psl/Math/log.php#L18)
- [max](./../src/Psl/Math/max.php#L19)
- [max_by](./../src/Psl/Math/max_by.php#L21)
- [maxva](./../src/Psl/Math/maxva.php#L20)
- [mean](./../src/Psl/Math/mean.php#L14)
- [median](./../src/Psl/Math/median.php#L15)
- [min](./../src/Psl/Math/min.php#L19)
- [min_by](./../src/Psl/Math/min_by.php#L29)
- [minva](./../src/Psl/Math/minva.php#L20)
- [round](./../src/Psl/Math/round.php#L19)
- [sin](./../src/Psl/Math/sin.php#L14)
- [sqrt](./../src/Psl/Math/sqrt.php#L20)
- [sum](./../src/Psl/Math/sum.php#L14)
- [sum_floats](./../src/Psl/Math/sum_floats.php#L14)
- [tan](./../src/Psl/Math/tan.php#L14)
- [to_base](./../src/Psl/Math/to_base.php#L18)


### `Psl\Observer`

#### `Interfaces`

- [SubjectInterface](./../src/Psl/Observer/SubjectInterface.php#L7)
- [ObserverInterface](./../src/Psl/Observer/ObserverInterface.php#L10)


### `Psl\Password`

#### `Constants`

- [DEFAULT_ALGORITHM](./../src/Psl/Password/constants.php#L0)
- [BCRYPT_ALGORITHM](./../src/Psl/Password/constants.php#L0)

#### `Functions`

- [algorithms](./../src/Psl/Password/algorithms.php#L14)
- [get_information](./../src/Psl/Password/get_information.php#L24)
- [hash](./../src/Psl/Password/hash.php#L32)
- [needs_rehash](./../src/Psl/Password/needs_rehash.php#L25)
- [verify](./../src/Psl/Password/verify.php#L14)


### `Psl\PseudoRandom`

#### `Functions`

- [float](./../src/Psl/PseudoRandom/float.php#L12)
- [int](./../src/Psl/PseudoRandom/int.php#L17)


### `Psl\Regex`

#### `Functions`

- [split](./../src/Psl/Regex/split.php#L29)
- [matches](./../src/Psl/Regex/matches.php#L19)
- [replace](./../src/Psl/Regex/replace.php#L26)
- [replace_with](./../src/Psl/Regex/replace_with.php#L26)
- [replace_every](./../src/Psl/Regex/replace_every.php#L27)


### `Psl\Result`

#### `Functions`

- [wrap](./../src/Psl/Result/wrap.php#L19)

#### `Interfaces`

- [ResultInterface](./../src/Psl/Result/ResultInterface.php#L19)

#### `Classes`

- [Failure](./../src/Psl/Result/Failure.php#L17)
- [Success](./../src/Psl/Result/Success.php#L17)


### `Psl\SecureRandom`

#### `Functions`

- [bytes](./../src/Psl/SecureRandom/bytes.php#L20)
- [float](./../src/Psl/SecureRandom/float.php#L14)
- [int](./../src/Psl/SecureRandom/int.php#L21)
- [string](./../src/Psl/SecureRandom/string.php#L25)


### `Psl\Shell`

#### `Functions`

- [escape_command](./../src/Psl/Shell/escape_command.php#L14)
- [escape_argument](./../src/Psl/Shell/escape_argument.php#L17)
- [execute](./../src/Psl/Shell/execute.php#L37)


### `Psl\Str`

#### `Constants`

- [ALPHABET](./../src/Psl/Str/constants.php#L0)
- [ALPHABET_ALPHANUMERIC](./../src/Psl/Str/constants.php#L0)

#### `Functions`

- [capitalize](./../src/Psl/Str/capitalize.php#L33)
- [capitalize_words](./../src/Psl/Str/capitalize_words.php#L35)
- [chr](./../src/Psl/Str/chr.php#L27)
- [chunk](./../src/Psl/Str/chunk.php#L40)
- [concat](./../src/Psl/Str/concat.php#L20)
- [contains](./../src/Psl/Str/contains.php#L42)
- [contains_ci](./../src/Psl/Str/contains_ci.php#L42)
- [detect_encoding](./../src/Psl/Str/detect_encoding.php#L21)
- [convert_encoding](./../src/Psl/Str/convert_encoding.php#L19)
- [is_utf8](./../src/Psl/Str/is_utf8.php#L14)
- [ends_with](./../src/Psl/Str/ends_with.php#L39)
- [ends_with_ci](./../src/Psl/Str/ends_with_ci.php#L39)
- [fold](./../src/Psl/Str/fold.php#L22)
- [format](./../src/Psl/Str/format.php#L26)
- [format_number](./../src/Psl/Str/format_number.php#L19)
- [from_code_points](./../src/Psl/Str/from_code_points.php#L20)
- [is_empty](./../src/Psl/Str/is_empty.php#L29)
- [join](./../src/Psl/Str/join.php#L27)
- [length](./../src/Psl/Str/length.php#L30)
- [levenshtein](./../src/Psl/Str/levenshtein.php#L29)
- [lowercase](./../src/Psl/Str/lowercase.php#L35)
- [metaphone](./../src/Psl/Str/metaphone.php#L19)
- [ord](./../src/Psl/Str/ord.php#L27)
- [pad_left](./../src/Psl/Str/pad_left.php#L36)
- [pad_right](./../src/Psl/Str/pad_right.php#L36)
- [repeat](./../src/Psl/Str/repeat.php#L28)
- [replace](./../src/Psl/Str/replace.php#L19)
- [replace_ci](./../src/Psl/Str/replace_ci.php#L20)
- [replace_every](./../src/Psl/Str/replace_every.php#L19)
- [replace_every_ci](./../src/Psl/Str/replace_every_ci.php#L19)
- [search](./../src/Psl/Str/search.php#L25)
- [search_ci](./../src/Psl/Str/search_ci.php#L25)
- [search_last](./../src/Psl/Str/search_last.php#L25)
- [search_last_ci](./../src/Psl/Str/search_last_ci.php#L25)
- [slice](./../src/Psl/Str/slice.php#L25)
- [splice](./../src/Psl/Str/splice.php#L22)
- [split](./../src/Psl/Str/split.php#L24)
- [starts_with](./../src/Psl/Str/starts_with.php#L16)
- [starts_with_ci](./../src/Psl/Str/starts_with_ci.php#L16)
- [strip_prefix](./../src/Psl/Str/strip_prefix.php#L17)
- [strip_suffix](./../src/Psl/Str/strip_suffix.php#L17)
- [to_int](./../src/Psl/Str/to_int.php#L12)
- [trim](./../src/Psl/Str/trim.php#L18)
- [trim_left](./../src/Psl/Str/trim_left.php#L18)
- [trim_right](./../src/Psl/Str/trim_right.php#L18)
- [truncate](./../src/Psl/Str/truncate.php#L29)
- [uppercase](./../src/Psl/Str/uppercase.php#L19)
- [width](./../src/Psl/Str/width.php#L19)
- [wrap](./../src/Psl/Str/wrap.php#L23)
- [after](./../src/Psl/Str/after.php#L15)
- [after_ci](./../src/Psl/Str/after_ci.php#L15)
- [after_last](./../src/Psl/Str/after_last.php#L15)
- [after_last_ci](./../src/Psl/Str/after_last_ci.php#L15)
- [before](./../src/Psl/Str/before.php#L15)
- [before_ci](./../src/Psl/Str/before_ci.php#L15)
- [before_last](./../src/Psl/Str/before_last.php#L15)
- [before_last_ci](./../src/Psl/Str/before_last_ci.php#L15)


### `Psl\Str\Byte`

#### `Functions`

- [capitalize](./../src/Psl/Str/Byte/capitalize.php#L17)
- [capitalize_words](./../src/Psl/Str/Byte/capitalize_words.php#L17)
- [chr](./../src/Psl/Str/Byte/chr.php#L14)
- [chunk](./../src/Psl/Str/Byte/chunk.php#L27)
- [compare](./../src/Psl/Str/Byte/compare.php#L19)
- [compare_ci](./../src/Psl/Str/Byte/compare_ci.php#L19)
- [contains](./../src/Psl/Str/Byte/contains.php#L21)
- [contains_ci](./../src/Psl/Str/Byte/contains_ci.php#L21)
- [ends_with](./../src/Psl/Str/Byte/ends_with.php#L12)
- [ends_with_ci](./../src/Psl/Str/Byte/ends_with_ci.php#L14)
- [length](./../src/Psl/Str/Byte/length.php#L14)
- [lowercase](./../src/Psl/Str/Byte/lowercase.php#L14)
- [ord](./../src/Psl/Str/Byte/ord.php#L12)
- [pad_left](./../src/Psl/Str/Byte/pad_left.php#L25)
- [pad_right](./../src/Psl/Str/Byte/pad_right.php#L25)
- [replace](./../src/Psl/Str/Byte/replace.php#L15)
- [replace_ci](./../src/Psl/Str/Byte/replace_ci.php#L15)
- [replace_every](./../src/Psl/Str/Byte/replace_every.php#L17)
- [replace_every_ci](./../src/Psl/Str/Byte/replace_every_ci.php#L17)
- [reverse](./../src/Psl/Str/Byte/reverse.php#L10)
- [rot13](./../src/Psl/Str/Byte/rot13.php#L14)
- [search](./../src/Psl/Str/Byte/search.php#L23)
- [search_ci](./../src/Psl/Str/Byte/search_ci.php#L23)
- [search_last](./../src/Psl/Str/Byte/search_last.php#L25)
- [search_last_ci](./../src/Psl/Str/Byte/search_last_ci.php#L23)
- [shuffle](./../src/Psl/Str/Byte/shuffle.php#L14)
- [slice](./../src/Psl/Str/Byte/slice.php#L22)
- [splice](./../src/Psl/Str/Byte/splice.php#L23)
- [split](./../src/Psl/Str/Byte/split.php#L25)
- [starts_with](./../src/Psl/Str/Byte/starts_with.php#L14)
- [starts_with_ci](./../src/Psl/Str/Byte/starts_with_ci.php#L14)
- [strip_prefix](./../src/Psl/Str/Byte/strip_prefix.php#L13)
- [strip_suffix](./../src/Psl/Str/Byte/strip_suffix.php#L13)
- [trim](./../src/Psl/Str/Byte/trim.php#L17)
- [trim_left](./../src/Psl/Str/Byte/trim_left.php#L17)
- [trim_right](./../src/Psl/Str/Byte/trim_right.php#L17)
- [uppercase](./../src/Psl/Str/Byte/uppercase.php#L14)
- [words](./../src/Psl/Str/Byte/words.php#L19)
- [wrap](./../src/Psl/Str/Byte/wrap.php#L21)
- [after](./../src/Psl/Str/Byte/after.php#L14)
- [after_ci](./../src/Psl/Str/Byte/after_ci.php#L14)
- [after_last](./../src/Psl/Str/Byte/after_last.php#L15)
- [after_last_ci](./../src/Psl/Str/Byte/after_last_ci.php#L14)
- [before](./../src/Psl/Str/Byte/before.php#L14)
- [before_ci](./../src/Psl/Str/Byte/before_ci.php#L14)
- [before_last](./../src/Psl/Str/Byte/before_last.php#L14)
- [before_last_ci](./../src/Psl/Str/Byte/before_last_ci.php#L14)


### `Psl\Str\Grapheme`

#### `Functions`

- [contains](./../src/Psl/Str/Grapheme/contains.php#L21)
- [contains_ci](./../src/Psl/Str/Grapheme/contains_ci.php#L21)
- [ends_with](./../src/Psl/Str/Grapheme/ends_with.php#L17)
- [ends_with_ci](./../src/Psl/Str/Grapheme/ends_with_ci.php#L17)
- [length](./../src/Psl/Str/Grapheme/length.php#L19)
- [search](./../src/Psl/Str/Grapheme/search.php#L24)
- [search_ci](./../src/Psl/Str/Grapheme/search_ci.php#L24)
- [search_last](./../src/Psl/Str/Grapheme/search_last.php#L25)
- [search_last_ci](./../src/Psl/Str/Grapheme/search_last_ci.php#L25)
- [slice](./../src/Psl/Str/Grapheme/slice.php#L21)
- [starts_with](./../src/Psl/Str/Grapheme/starts_with.php#L12)
- [starts_with_ci](./../src/Psl/Str/Grapheme/starts_with_ci.php#L12)
- [strip_prefix](./../src/Psl/Str/Grapheme/strip_prefix.php#L13)
- [strip_suffix](./../src/Psl/Str/Grapheme/strip_suffix.php#L13)
- [after](./../src/Psl/Str/Grapheme/after.php#L14)
- [after_ci](./../src/Psl/Str/Grapheme/after_ci.php#L14)
- [after_last](./../src/Psl/Str/Grapheme/after_last.php#L15)
- [after_last_ci](./../src/Psl/Str/Grapheme/after_last_ci.php#L14)
- [before](./../src/Psl/Str/Grapheme/before.php#L14)
- [before_ci](./../src/Psl/Str/Grapheme/before_ci.php#L14)
- [before_last](./../src/Psl/Str/Grapheme/before_last.php#L14)
- [before_last_ci](./../src/Psl/Str/Grapheme/before_last_ci.php#L14)


### `Psl\Type`

#### `Functions`

- [map](./../src/Psl/Type/map.php#L21)
- [mutable_map](./../src/Psl/Type/mutable_map.php#L21)
- [vector](./../src/Psl/Type/vector.php#L19)
- [mutable_vector](./../src/Psl/Type/mutable_vector.php#L19)
- [array_key](./../src/Psl/Type/array_key.php#L10)
- [bool](./../src/Psl/Type/bool.php#L10)
- [float](./../src/Psl/Type/float.php#L10)
- [int](./../src/Psl/Type/int.php#L10)
- [intersection](./../src/Psl/Type/intersection.php#L20)
- [iterable](./../src/Psl/Type/iterable.php#L20)
- [mixed](./../src/Psl/Type/mixed.php#L10)
- [null](./../src/Psl/Type/null.php#L10)
- [nullable](./../src/Psl/Type/nullable.php#L18)
- [optional](./../src/Psl/Type/optional.php#L14)
- [num](./../src/Psl/Type/num.php#L10)
- [object](./../src/Psl/Type/object.php#L14)
- [resource](./../src/Psl/Type/resource.php#L12)
- [string](./../src/Psl/Type/string.php#L10)
- [non_empty_string](./../src/Psl/Type/non_empty_string.php#L10)
- [scalar](./../src/Psl/Type/scalar.php#L10)
- [shape](./../src/Psl/Type/shape.php#L15)
- [union](./../src/Psl/Type/union.php#L20)
- [vec](./../src/Psl/Type/vec.php#L18)
- [dict](./../src/Psl/Type/dict.php#L20)
- [is_array](./../src/Psl/Type/is_array.php#L20) ( deprecated )
- [is_arraykey](./../src/Psl/Type/is_arraykey.php#L18) ( deprecated )
- [is_bool](./../src/Psl/Type/is_bool.php#L20) ( deprecated )
- [is_callable](./../src/Psl/Type/is_callable.php#L18)
- [is_float](./../src/Psl/Type/is_float.php#L20) ( deprecated )
- [is_instanceof](./../src/Psl/Type/is_instanceof.php#L22) ( deprecated )
- [is_int](./../src/Psl/Type/is_int.php#L20) ( deprecated )
- [is_iterable](./../src/Psl/Type/is_iterable.php#L20) ( deprecated )
- [is_nan](./../src/Psl/Type/is_nan.php#L14)
- [is_null](./../src/Psl/Type/is_null.php#L18) ( deprecated )
- [is_numeric](./../src/Psl/Type/is_numeric.php#L20) ( deprecated )
- [is_object](./../src/Psl/Type/is_object.php#L20) ( deprecated )
- [is_resource](./../src/Psl/Type/is_resource.php#L22) ( deprecated )
- [is_scalar](./../src/Psl/Type/is_scalar.php#L20) ( deprecated )
- [is_string](./../src/Psl/Type/is_string.php#L20) ( deprecated )
- [literal_scalar](./../src/Psl/Type/literal_scalar.php#L18)

#### `Interfaces`

- [TypeInterface](./../src/Psl/Type/TypeInterface.php#L14)

#### `Classes`

- [Type](./../src/Psl/Type/Type.php#L15)


### `Psl\Vec`

#### `Functions`

- [chunk](./../src/Psl/Vec/chunk.php#L24)
- [chunk_with_keys](./../src/Psl/Vec/chunk_with_keys.php#L27)
- [concat](./../src/Psl/Vec/concat.php#L17)
- [enumerate](./../src/Psl/Vec/enumerate.php#L17)
- [fill](./../src/Psl/Vec/fill.php#L24)
- [filter](./../src/Psl/Vec/filter.php#L30)
- [filter_keys](./../src/Psl/Vec/filter_keys.php#L31)
- [filter_nulls](./../src/Psl/Vec/filter_nulls.php#L20)
- [filter_with_key](./../src/Psl/Vec/filter_with_key.php#L34)
- [flat_map](./../src/Psl/Vec/flat_map.php#L16)
- [keys](./../src/Psl/Vec/keys.php#L17)
- [partition](./../src/Psl/Vec/partition.php#L18)
- [range](./../src/Psl/Vec/range.php#L50)
- [reductions](./../src/Psl/Vec/reductions.php#L27)
- [map](./../src/Psl/Vec/map.php#L27)
- [map_with_key](./../src/Psl/Vec/map_with_key.php#L27)
- [reproduce](./../src/Psl/Vec/reproduce.php#L25)
- [reverse](./../src/Psl/Vec/reverse.php#L22)
- [shuffle](./../src/Psl/Vec/shuffle.php#L26)
- [sort](./../src/Psl/Vec/sort.php#L23)
- [sort_by](./../src/Psl/Vec/sort_by.php#L26)
- [values](./../src/Psl/Vec/values.php#L16)
- [zip](./../src/Psl/Vec/zip.php#L37)



---

> This markdown file was generated using `docs/documenter.php`.
>
> Any edits to it will likely be lost.
